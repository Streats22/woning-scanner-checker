/**
 * Publieke basis-URL voor canonieke links en JSON-LD (`NUXT_PUBLIC_SITE_URL`, anders request-origin).
 */
export function useWscSiteBase() {
  const config = useRuntimeConfig()

  return computed(() => {
    const fromConfig = (config.public.siteUrl as string)?.trim().replace(/\/$/, '') || ''
    if (fromConfig)
      return fromConfig

    if (import.meta.server) {
      try {
        return useRequestURL().origin
      }
      catch {
        /* ignore */
      }
    }

    if (import.meta.client && typeof window !== 'undefined')
      return window.location.origin

    return ''
  })
}
