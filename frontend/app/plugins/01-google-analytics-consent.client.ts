/**
 * Google Analytics 4 — alleen bij functionele privacy-toestemming.
 * Geen script bij “Alleen noodzakelijk” of vóór keuze. Meet-ID via NUXT_PUBLIC_GOOGLE_ANALYTICS_ID.
 */
export default defineNuxtPlugin(() => {
  if (import.meta.server)
    return

  const id = useRuntimeConfig().public.googleAnalyticsId?.trim()
  if (!id)
    return

  const router = useRouter()
  const { consent } = useCookieConsent()

  let scriptInjected = false
  let gtagReady = false

  function sendPageView(path: string) {
    const g = window.gtag
    if (typeof g !== 'function')
      return
    g('event', 'page_view', { page_path: path })
  }

  function injectGtag(measurementId: string) {
    if (scriptInjected)
      return
    scriptInjected = true

    window.dataLayer = window.dataLayer ?? []
    window.gtag = function gtag(...args: unknown[]) {
      window.dataLayer!.push(args)
    }

    const script = document.createElement('script')
    script.async = true
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`
    script.onload = () => {
      window.gtag!('js', new Date())
      window.gtag!('config', measurementId, { send_page_view: false })
      gtagReady = true
      sendPageView(router.currentRoute.value.fullPath)
    }
    document.head.appendChild(script)
  }

  watch(
    consent,
    (c) => {
      if (c?.functional === true)
        injectGtag(id)
    },
    { immediate: true },
  )

  router.afterEach((to, from) => {
    if (!gtagReady || typeof window.gtag !== 'function')
      return
    /** Eerste route: page_view zit in script.onload; daarna alleen SPA-navigatie. */
    if (from.matched.length === 0)
      return
    sendPageView(to.fullPath)
  })
})
