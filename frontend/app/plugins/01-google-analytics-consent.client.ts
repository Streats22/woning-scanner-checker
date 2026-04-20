/**
 * Consent Mode update + page_view na toestemming, of direct als `gaDefaultApproved` (tijdelijk).
 * gtag/js + basis-config staan in google-tag-bootstrap.ts (zichtbaar voor Google Tag Assistant).
 */
export default defineNuxtPlugin(() => {
  if (import.meta.server)
    return

  const id = useRuntimeConfig().public.googleAnalyticsId?.trim()
  if (!id)
    return

  const defaultApproved = useRuntimeConfig().public.gaDefaultApproved === true

  const router = useRouter()
  const { consent } = useCookieConsent()

  let activated = false

  function sendPageView(path: string) {
    const g = window.gtag
    if (typeof g !== 'function')
      return
    g('event', 'page_view', { page_path: path })
  }

  function mayUseAnalytics(): boolean {
    if (consent.value?.functional === false)
      return false
    if (defaultApproved)
      return true
    return consent.value?.functional === true
  }

  function activateMeasurement() {
    if (activated)
      return
    if (!mayUseAnalytics())
      return
    activated = true
    window.gtag?.('consent', 'update', {
      analytics_storage: 'granted',
    })
    sendPageView(router.currentRoute.value.fullPath)
  }

  function revokeMeasurement() {
    if (!activated)
      return
    activated = false
    window.gtag?.('consent', 'update', {
      analytics_storage: 'denied',
    })
  }

  watch(
    consent,
    () => {
      if (mayUseAnalytics())
        activateMeasurement()
      else
        revokeMeasurement()
    },
    { immediate: true },
  )

  router.afterEach((to, from) => {
    if (!activated || typeof window.gtag !== 'function')
      return
    if (from.matched.length === 0)
      return
    sendPageView(to.fullPath)
  })
})
