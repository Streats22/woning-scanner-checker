/**
 * Bestaande bezoekers hebben mogelijk nog een oude Workbox-service-worker + precache
 * (404 op /_nuxt/* na deploy, rare navigatie op mobiel).
 * Zolang we geen SW registreren (zie nuxt.config pwa.injectRegister), ruimen we die hier op.
 *
 * Zet NUXT_PUBLIC_ENABLE_PWA_SW=1 om dit te overslaan en de service worker weer te gebruiken.
 */
export default defineNuxtPlugin({
  name: 'wsc-unregister-sw',
  enforce: 'pre',
  setup() {
    if (import.meta.dev)
      return

    const config = useRuntimeConfig()
    if (config.public.pwaSwEnabled === true)
      return

    void (async () => {
      if ('serviceWorker' in navigator) {
        try {
          const regs = await navigator.serviceWorker.getRegistrations()
          await Promise.all(regs.map(r => r.unregister()))
        }
        catch {
          /* ignore */
        }
      }

      if (!('caches' in globalThis))
        return
      try {
        const keys = await caches.keys()
        const stale = keys.filter((name) => {
          if (/google-fonts/i.test(name))
            return false
          return (
            /workbox/i.test(name)
            || /precache/i.test(name)
            || /^wsc-nuxt-chunks$/i.test(name)
          )
        })
        await Promise.all(stale.map(k => caches.delete(k)))
      }
      catch {
        /* ignore */
      }
    })()
  },
})
