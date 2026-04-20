// https://nuxt.com/docs/api/configuration/nuxt-config
// API: NUXT_PUBLIC_API_BASE (productie: https://… of leeg) of NUXT_LARAVEL_URL (lokaal: Valet/.test).
// Leeg = zelfde origin → /api/... (aanbevolen als Nuxt en Laravel op één domein draaien).
import { normalizeApiBase } from './app/utils/wscApiBase'

const apiAllowHttp = process.env.NUXT_PUBLIC_API_ALLOW_HTTP === '1'

const resolvedApiBase = normalizeApiBase(
  process.env.NUXT_PUBLIC_API_BASE?.trim()
    || process.env.NUXT_LARAVEL_URL?.trim()
    || '',
  {
    serverProductionBuild: process.env.NODE_ENV === 'production',
    allowHttp: apiAllowHttp,
  },
)

/** Tijdstip/commit van `nuxt build` — zichtbaar in footer om productie vs lokaal te vergelijken. */
const buildId =
  process.env.NUXT_PUBLIC_BUILD_ID?.trim()
  || new Date().toISOString()

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: process.env.NODE_ENV !== 'production' },

  modules: ['@vite-pwa/nuxt', '@nuxtjs/color-mode', '@nuxtjs/i18n'],

  /**
   * Standaard geen service worker registreren: Workbox-precache gaf op mobiel na deploys 404 op oude /_nuxt-hashes.
   * Webmanifest blijft (icoon / “toevoegen aan startscherm”). Zie plugin 00-unregister-sw voor opruimen oude SW.
   * Zet NUXT_PUBLIC_ENABLE_PWA_SW=1 om SW + periodicSync weer aan te zetten (experimenteel).
   */
  pwa: {
    /**
     * sw.js die nog binnenkomt (oude installatie) haalt zichzelf uit + caches; helpt klanten
     * die niet meteen de nieuwe bundle laden. Werkt samen met plugin 00-unregister-sw.
     */
    selfDestroying: true,
    injectRegister: process.env.NUXT_PUBLIC_ENABLE_PWA_SW === '1' ? 'auto' : false,
    registerType: 'autoUpdate',
    ...(process.env.NUXT_PUBLIC_ENABLE_PWA_SW === '1'
      ? {
          client: {
            periodicSyncForUpdates: 3600,
          },
        }
      : {}),
    manifest: {
      name: 'De Huur Radar',
      short_name: 'Huur Radar',
      description: 'Huurfraude-check en risicoscore vóór je betaalt.',
      theme_color: '#1e40af',
      background_color: '#f3f6fb',
      display: 'standalone',
      lang: 'nl',
      start_url: '/',
      scope: '/',
    },
    workbox: {
      navigateFallback: '/',
      /**
       * Alleen echte app-routes krijgen offline de shell (`/`). Anders vangen onbekende paden
       * dezelfde fallback als de startpagina (oneindig “laden” / geen echte 404) — zie o.a.
       * https://stackoverflow.com/questions/66804699/nginx-nuxt-website-not-redirecting-to-404
       */
      navigateFallbackAllowlist: [/^\/$/, /^\/check\/?$/, /^\/faq\/?$/],
      navigateFallbackDenylist: [/^\/api/, /^\/report/, /^\/build/, /^\/storage/, /^\/up$/],
      globPatterns: ['**/*.{js,css,ico,png,svg,woff2}'],
      /** Standaard in vite-plugin-pwa; expliciet voor duidelijkheid bij oude precache-entries. */
      cleanupOutdatedCaches: true,
      runtimeCaching: [
        {
          /** Hash-bestanden: bij voorkeur netwerk (nieuwe deploy), anders runtime-cache als fallback. */
          urlPattern: /^https?:\/\/[^/]+\/_nuxt\//i,
          handler: 'NetworkFirst',
          options: {
            cacheName: 'wsc-nuxt-chunks',
            expiration: { maxEntries: 80, maxAgeSeconds: 60 * 60 * 24 * 7 },
            networkTimeoutSeconds: 8,
          },
        },
        {
          urlPattern: /^https:\/\/fonts\.(?:googleapis|gstatic)\.com\/.*/i,
          handler: 'CacheFirst',
          options: {
            cacheName: 'google-fonts',
            expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 365 },
          },
        },
      ],
    },
    devOptions: {
      enabled: false,
    },
  },

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
      viewport: 'width=device-width, initial-scale=1, viewport-fit=cover',
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
      /** Productie: canonieke URL, Open Graph/WhatsApp (og:url + og:image), sitemap, JSON-LD */
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL?.trim() || '',
      apiBase: resolvedApiBase,
      /** Alleen `true` als `NUXT_PUBLIC_API_ALLOW_HTTP=1` — geen http→https upgrade voor API-base. */
      apiAllowHttp,
      /** ISO-tijd of git-SHA (set NUXT_PUBLIC_BUILD_ID in CI) */
      buildId,
      /** `true` als NUXT_PUBLIC_ENABLE_PWA_SW=1 — schakelt unregister-plugin uit en registreert de SW. */
      pwaSwEnabled: process.env.NUXT_PUBLIC_ENABLE_PWA_SW === '1',
      /**
       * Google Analytics 4 measurement ID (bijv. G-XXXXXXXX). Leeg = geen GA.
       * Wordt alleen geladen na expliciete keuze “Functionele opslag toestaan” (geen laden bij “Alleen noodzakelijk”).
       */
      googleAnalyticsId: process.env.NUXT_PUBLIC_GOOGLE_ANALYTICS_ID?.trim() || '',
    },
  },

  // Voorkomt dat oude HTML (zonder nieuwe /_nuxt/-hashes) te lang gecached wordt door Cloudflare/browser.
  nitro: {
    routeRules: {
      '/': { headers: { 'cache-control': 'private, no-cache, must-revalidate' } },
      '/check': { headers: { 'cache-control': 'private, no-cache, must-revalidate' } },
      '/faq': { headers: { 'cache-control': 'private, no-cache, must-revalidate' } },
      /** Voorkomt dat een tussen-cache oude sw.js vasthoudt — mobiel krijgt updates sneller door. */
      '/sw.js': { headers: { 'cache-control': 'no-store, must-revalidate' } },
      '/registerSW.js': { headers: { 'cache-control': 'no-store, must-revalidate' } },
      /** OG-image voor WhatsApp/Facebook; mag edge-cachen, bij wijziging bestandsnaam of cache purgen. */
      '/og.png': { headers: { 'cache-control': 'public, max-age=86400, stale-while-revalidate=604800' } },
    },
  },
})
