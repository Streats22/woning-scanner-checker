/**
 * Bestaande bezoekers hebben mogelijk nog een oude Workbox-service-worker + precache
 * (404 op /_nuxt/* na deploy, rare navigatie op mobiel — o.a. Firefox).
 * Zolang we geen SW registreren (zie nuxt.config pwa.injectRegister), ruimen we die hier op.
 *
 * Zet NUXT_PUBLIC_ENABLE_PWA_SW=1 om dit te overslaan en de service worker weer te gebruiken.
 */
const RELOAD_FLAG = 'wsc-sw-cleared-reload-v3'

function isFirefox(): boolean {
  if (typeof navigator === 'undefined')
    return false
  return /firefox/i.test(navigator.userAgent) && !/seamonkey/i.test(navigator.userAgent)
}

/** Workbox kan IndexedDB naast Cache API gebruiken; Firefox houdt dat soms vast na unregister. */
async function deleteWorkboxIndexedDatabases(): Promise<void> {
  const idb = globalThis.indexedDB
  if (!idb || typeof idb.databases !== 'function')
    return
  try {
    const list = await idb.databases()
    await Promise.all(
      list
        .filter((d): d is { name: string } => typeof d?.name === 'string')
        .filter(d => /workbox|precache|webpack|wsc-/i.test(d.name))
        .map(d => new Promise<void>((resolve) => {
          const req = idb.deleteDatabase(d.name)
          req.onsuccess = req.onerror = () => resolve()
        })),
    )
  }
  catch {
    /* ignore */
  }
}

async function clearServiceWorkerAndCaches(): Promise<{ hadRegs: boolean; deletedCaches: number }> {
  let hadRegs = false

  const swAttempts = isFirefox() ? 6 : 2
  const swDelayMs = isFirefox() ? 500 : 400

  if ('serviceWorker' in navigator) {
    try {
      // Firefox (zeker mobiel): registraties zijn vaak later zichtbaar — meerdere rondes + langere delay.
      for (let attempt = 0; attempt < swAttempts; attempt++) {
        if (attempt > 0)
          await new Promise(r => setTimeout(r, swDelayMs))
        const regs = await navigator.serviceWorker.getRegistrations()
        if (regs.length)
          hadRegs = true
        await Promise.all(regs.map(r => r.unregister()))
      }
    }
    catch {
      /* ignore */
    }
  }

  let deletedCaches = 0
  if ('caches' in globalThis) {
    try {
      const keys = await caches.keys()
      const stale = keys.filter((name) => {
        if (/google-fonts/i.test(name))
          return false
        return (
          /workbox/i.test(name)
          || /precache/i.test(name)
          || /wsc-/i.test(name)
          || /pwa/i.test(name)
          || /nuxt/i.test(name)
        )
      })
      deletedCaches = stale.length
      await Promise.all(stale.map(k => caches.delete(k)))
    }
    catch {
      /* ignore */
    }
  }

  if (isFirefox())
    await deleteWorkboxIndexedDatabases()

  return { hadRegs, deletedCaches }
}

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
      const { hadRegs, deletedCaches } = await clearServiceWorkerAndCaches()

      // Eén harde reload: laadt verse HTML/JS zonder oude SW-cache (helpt Firefox).
      let mayReload = false
      try {
        mayReload = !sessionStorage.getItem(RELOAD_FLAG)
      }
      catch {
        /* privévenster / strikte modus: geen reload forceren (loop-risico) */
      }
      if ((hadRegs || deletedCaches > 0) && mayReload) {
        try {
          sessionStorage.setItem(RELOAD_FLAG, '1')
        }
        catch {
          /* zonder flag geen reload */
          return
        }
        window.location.reload()
      }
    })()
  },
})
