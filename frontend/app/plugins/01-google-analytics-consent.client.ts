/**
 * nuxt-gtag (initMode: manual): laadt gtag.js pas na toestemming.
 * Consent Mode v2: default denied in nuxt.config → hier `update` + page_view bij akkoord.
 * Zie https://nuxt.com/modules/gtag
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
  const { gtag, initialize, disableAnalytics, enableAnalytics } = useGtag()

  let activated = false

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
    initialize()
    enableAnalytics()
    nextTick(() => {
      gtag('consent', 'update', {
        analytics_storage: 'granted',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
      })
      useTrackEvent('page_view', {
        page_path: router.currentRoute.value.fullPath,
      })
    })
  }

  function revokeMeasurement() {
    if (!activated)
      return
    activated = false
    gtag('consent', 'update', {
      analytics_storage: 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
    })
    disableAnalytics()
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
    if (!activated)
      return
    if (from.matched.length === 0)
      return
    useTrackEvent('page_view', { page_path: to.fullPath })
  })
})
