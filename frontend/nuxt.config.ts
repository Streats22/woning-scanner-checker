// https://nuxt.com/docs/api/configuration/nuxt-config
// API: NUXT_PUBLIC_API_BASE (productie: https://dehuurradar.nl) of NUXT_LARAVEL_URL (lokaal: Valet/.test).
// Leeg = zelfde origin → /api/... (aanbevolen als Nuxt en Laravel op één domein draaien).
const resolvedApiBase =
  process.env.NUXT_PUBLIC_API_BASE?.trim()
  || process.env.NUXT_LARAVEL_URL?.trim()
  || ''

/** Tijdstip/commit van `nuxt build` — zichtbaar in footer om productie vs lokaal te vergelijken. */
const buildId =
  process.env.NUXT_PUBLIC_BUILD_ID?.trim()
  || new Date().toISOString()

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: process.env.NODE_ENV !== 'production' },

  modules: ['@nuxtjs/color-mode', '@nuxtjs/i18n'],

  css: ['~/assets/css/main.css'],

  vite: {
    optimizeDeps: {
      include: ['@vue/devtools-core', '@vue/devtools-kit'],
    },
  },

  colorMode: {
    classSuffix: '',
    preference: 'system',
    fallback: 'light',
    storageKey: 'wsc-color-mode',
  },

  i18n: {
    locales: [
      { code: 'nl', language: 'nl-NL', name: 'Nederlands', file: 'nl.json' },
      { code: 'en', language: 'en-US', name: 'English', file: 'en.json' },
    ],
    defaultLocale: 'nl',
    // false: alle vertalingen in de server-bundle — SSR-HTML bevat altijd o.a. merknaam (lazy + SSR gaf lege/oude tekst op productie).
    lazy: false,
    langDir: 'locales',
    strategy: 'no_prefix',
    detectBrowserLanguage: {
      // Geen taal-cookie: voorkeur alleen na expliciete toestemming (localStorage via plugin).
      useCookie: false,
      cookieKey: 'wsc_i18n',
      fallbackLocale: 'nl',
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'nl' },
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
      meta: [
        { name: 'format-detection', content: 'telephone=no' },
        { name: 'referrer', content: 'strict-origin-when-cross-origin' },
        /** Altijd in <head> na build — eenvoudig te checken: curl … | grep x-wsc-build */
        { name: 'x-wsc-build', content: buildId },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@500;600;700&display=swap',
        },
      ],
    },
  },

  runtimeConfig: {
    public: {
      /** Productie: canonieke URL, Open Graph, sitemap (bv. https://dehuurradar.nl) */
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL?.trim() || '',
      apiBase: resolvedApiBase,
      /** ISO-tijd of git-SHA (set NUXT_PUBLIC_BUILD_ID in CI) */
      buildId,
    },
  },

  // Voorkomt dat oude HTML (zonder nieuwe /_nuxt/-hashes) te lang gecached wordt door Cloudflare/browser.
  nitro: {
    routeRules: {
      '/': { headers: { 'cache-control': 'private, no-cache, must-revalidate' } },
      '/faq': { headers: { 'cache-control': 'private, no-cache, must-revalidate' } },
    },
  },
})
