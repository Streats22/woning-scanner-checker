// https://nuxt.com/docs/api/configuration/nuxt-config
// Standaard: Valet (http://… .test). Voor `php artisan serve`: zet NUXT_LARAVEL_URL=http://127.0.0.1:8000 in frontend/.env
const laravelOrigin = process.env.NUXT_LARAVEL_URL || 'http://woning-scam-checker.test'
// Direct naar Laravel (geen proxy). Lege NUXT_PUBLIC_API_BASE in .env overschrijft apiBase — apiBaseDefault vangt dat af.
const publicApiBase = process.env.NUXT_PUBLIC_API_BASE?.trim() || laravelOrigin

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

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
    lazy: true,
    // Bestanden: i18n/locales/*.json (module voegt zelf het i18n/-segment toe)
    langDir: 'locales',
    strategy: 'no_prefix',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'wsc_i18n',
      fallbackLocale: 'nl',
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'nl' },
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
      apiBase: publicApiBase,
      // Niet leegzetten via .env — blijft altijd de Laravel-URL (fallback als NUXT_PUBLIC_API_BASE per ongeluk leeg is).
      apiBaseDefault: laravelOrigin,
    },
  },
})
