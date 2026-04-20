import type { MaybeRefOrGetter } from 'vue'

export interface WscSeoOptions {
  title: MaybeRefOrGetter<string>
  description: MaybeRefOrGetter<string>
  /** Pad zonder origin, bv. `/` of `/faq`. Default: huidige route. */
  path?: MaybeRefOrGetter<string>
  noindex?: MaybeRefOrGetter<boolean>
}

/**
 * Canonieke URL’s, Open Graph, Twitter, hreflang en robots — afgestemd op `NUXT_PUBLIC_SITE_URL`.
 */
export function useWscSeo(options: WscSeoOptions) {
  const route = useRoute()
  const { locale } = useI18n()
  const config = useRuntimeConfig()

  const siteUrl = computed(() => (config.public.siteUrl as string)?.replace(/\/$/, '') || '')

  const path = computed(() => {
    const p = toValue(options.path ?? route.path)
    return p.startsWith('/') ? p : `/${p}`
  })

  const canonical = computed(() => {
    const base = siteUrl.value
    const p = path.value
    if (base) {
      return `${base}${p === '/' ? '' : p}`
    }
    if (import.meta.server) {
      try {
        const u = useRequestURL()
        return `${u.origin}${p}`
      }
      catch {
        /* ignore */
      }
    }
    if (import.meta.client && typeof window !== 'undefined')
      return `${window.location.origin}${p}`

    return ''
  })

  const title = computed(() => toValue(options.title))
  const description = computed(() => toValue(options.description))
  const noindex = computed(() => toValue(options.noindex ?? false))

  const ogLocale = computed(() => (locale.value === 'en' ? 'en_US' : 'nl_NL'))
  const ogLocaleAlternate = computed(() => (locale.value === 'en' ? ['nl_NL'] : ['en_US']))

  useSeoMeta({
    title: title,
    description: description,
    ogTitle: title,
    ogDescription: description,
    ogSiteName: 'De Huur Radar',
    ogType: 'website',
    ogUrl: canonical,
    ogLocale: ogLocale,
    ogLocaleAlternate: ogLocaleAlternate,
    twitterCard: 'summary_large_image',
    twitterTitle: title,
    twitterDescription: description,
    robots: computed(() =>
      noindex.value
        ? 'noindex, nofollow'
        : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
    ),
  })

  useHead(() => ({
    meta: [
      { name: 'author', content: 'De Huur Radar' },
      { name: 'publisher', content: 'De Huur Radar' },
      { name: 'googlebot', content: 'index, follow, max-image-preview:large' },
    ],
    link: canonical.value
      ? [
          { rel: 'canonical', href: canonical.value },
          { rel: 'alternate', hreflang: 'nl-NL', href: canonical.value },
          { rel: 'alternate', hreflang: 'en', href: canonical.value },
          { rel: 'alternate', hreflang: 'x-default', href: canonical.value },
        ]
      : [],
  }))
}
