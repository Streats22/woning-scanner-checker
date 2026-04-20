import type { MaybeRefOrGetter } from 'vue'

export interface WscSeoOptions {
  title: MaybeRefOrGetter<string>
  description: MaybeRefOrGetter<string>
  /** Pad zonder origin, bv. `/` of `/faq`. Default: huidige route. */
  path?: MaybeRefOrGetter<string>
  noindex?: MaybeRefOrGetter<boolean>
  /** Volledige URL naar OG/Twitter-afbeelding; default: `{siteUrl}/og.png` */
  image?: MaybeRefOrGetter<string | undefined>
  /** Alt-tekst voor og:image / twitter:image (toegankelijkheid + WhatsApp/Facebook) */
  imageAlt?: MaybeRefOrGetter<string | undefined>
}

/** Moet overeenkomen met `public/og.png` (sips -Z 1200 …). */
const OG_WIDTH = 1200
const OG_HEIGHT = 669

/** WhatsApp/Facebook crawlers: HTTPS + absolute URL. */
function normalizeOgImageUrl(url: string): string {
  if (!url)
    return ''
  try {
    const u = new URL(url)
    if (u.protocol === 'http:' && u.hostname !== 'localhost' && u.hostname !== '127.0.0.1')
      u.protocol = 'https:'
    return u.toString()
  }
  catch {
    return url
  }
}

/**
 * Canonieke URL’s, Open Graph, Twitter, hreflang en robots — afgestemd op `NUXT_PUBLIC_SITE_URL`.
 * Standaard social image: `/og.png` (1200×669) voor WhatsApp, Facebook, LinkedIn, X.
 */
export function useWscSeo(options: WscSeoOptions) {
  const route = useRoute()
  const { locale, t } = useI18n()
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

  const ogImageUrl = computed(() => {
    const custom = toValue(options.image)
    if (custom?.trim())
      return normalizeOgImageUrl(custom.trim())
    const rel = '/og.png'
    const base = siteUrl.value
    let out = ''
    if (base)
      out = `${base}${rel}`
    else if (import.meta.server) {
      try {
        const u = useRequestURL()
        out = `${u.origin}${rel}`
      }
      catch {
        /* ignore */
      }
    }
    else if (import.meta.client && typeof window !== 'undefined')
      out = `${window.location.origin}${rel}`

    return normalizeOgImageUrl(out)
  })

  const ogImageAlt = computed(
    () => toValue(options.imageAlt) || t('meta.ogImageAlt'),
  )

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
    ogImage: () => ogImageUrl.value || undefined,
    ogImageUrl: () => ogImageUrl.value || undefined,
    ogImageSecureUrl: () =>
      ogImageUrl.value?.startsWith('https://') ? ogImageUrl.value : undefined,
    ogImageWidth: () => (ogImageUrl.value ? OG_WIDTH : undefined),
    ogImageHeight: () => (ogImageUrl.value ? OG_HEIGHT : undefined),
    ogImageAlt: () => (ogImageUrl.value ? ogImageAlt.value : undefined),
    ogImageType: () => (ogImageUrl.value ? 'image/png' : undefined),
    twitterCard: 'summary_large_image',
    twitterTitle: title,
    twitterDescription: description,
    twitterImage: () => ogImageUrl.value || undefined,
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
          { rel: 'alternate', hreflang: 'x-default', href: canonical.value },
        ]
      : [],
  }))
}
