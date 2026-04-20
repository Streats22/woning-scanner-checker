<template>
  <div class="layout-root">
    <a href="#main-content" class="skip-link">{{ t('a11y.skipToContent') }}</a>
    <header class="topbar">
      <div class="topbar__inner">
        <NuxtLink :to="localePath('/')" class="brand brand--link" :aria-label="t('header.navHome')">
          <span class="brand__mark">
            <BrandMark :size="40" decorative />
          </span>
          <div class="brand__text">
            <span class="brand__name font-display">{{ t('brand.name') }}</span>
            <span class="brand__badge">{{ t('brand.badge') }}</span>
          </div>
        </NuxtLink>

        <nav class="topbar__actions" :aria-label="t('a11y.siteNav')">
          <NuxtLink class="topbar__faq" :to="localePath('/faq')">{{ t('header.faq') }}</NuxtLink>
          <div
            class="seg seg--2"
            :class="locale === 'nl' ? 'seg--i0' : 'seg--i1'"
            role="group"
            :aria-label="t('header.language')"
          >
            <span class="seg__pill" aria-hidden="true" />
            <button
              type="button"
              class="seg__btn"
              :class="{ 'seg__btn--active': locale === 'nl' }"
              :aria-pressed="locale === 'nl'"
              :aria-label="t('header.langNl')"
              @click="setLocale('nl')"
            >
              NL
            </button>
            <button
              type="button"
              class="seg__btn"
              :class="{ 'seg__btn--active': locale === 'en' }"
              :aria-pressed="locale === 'en'"
              :aria-label="t('header.langEn')"
              @click="setLocale('en')"
            >
              EN
            </button>
          </div>

          <div
            class="seg seg--3"
            :class="'seg--i' + themeSegIndex"
            role="group"
            :aria-label="`${t('header.themeLight')}, ${t('header.themeSystem')}, ${t('header.themeDark')}`"
          >
            <span class="seg__pill" aria-hidden="true" />
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'light' }"
              :aria-pressed="colorMode.preference === 'light'"
              :aria-label="t('header.themeLight')"
              :title="t('header.themeLight')"
              @click="colorMode.preference = 'light'"
            >
              <span class="seg__glyph" aria-hidden="true">☀</span>
            </button>
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'system' }"
              :aria-pressed="colorMode.preference === 'system'"
              :aria-label="t('header.themeSystem')"
              :title="t('header.themeSystem')"
              @click="colorMode.preference = 'system'"
            >
              <span class="seg__glyph" aria-hidden="true">◐</span>
            </button>
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'dark' }"
              :aria-pressed="colorMode.preference === 'dark'"
              :aria-label="t('header.themeDark')"
              :title="t('header.themeDark')"
              @click="colorMode.preference = 'dark'"
            >
              <span class="seg__glyph" aria-hidden="true">☾</span>
            </button>
          </div>
        </nav>
      </div>
    </header>

    <main id="main-content" class="layout-main" tabindex="-1">
      <slot />
    </main>

    <footer class="footer">
      <p class="footer__site-links">
        <NuxtLink class="footer__site-link" :to="localePath('/faq')">{{ t('header.faq') }}</NuxtLink>
      </p>
      <p class="footer__highlight">{{ t('cookies.footerSummary') }}</p>
      <p class="footer__a11y" role="note">{{ t('a11y.footerStatement') }}</p>
      <p class="footer__text">{{ t('cookies.notApplicableLine') }}</p>
      <p class="footer__text">{{ t('footer.note') }}</p>
      <p class="footer__text footer__credit">
        {{ t('footer.madeBy') }}
        <a
          class="footer__inline-link"
          href="https://streatsdesign.nl"
          target="_blank"
          rel="noopener noreferrer"
        >StreatsDesign</a>
        <span class="footer__credit-sep" aria-hidden="true">·</span>
        <span>{{ t('footer.contactLabel') }}</span>
        <a
          class="footer__inline-link"
          href="mailto:streatsDesign@outlook.com"
        >streatsDesign@outlook.com</a>
      </p>
      <button type="button" class="footer__link" @click="openCookieSettings">
        {{ t('cookies.openSettings') }}
      </button>
      <p
        v-if="buildIdDisplay"
        class="footer__build"
        :title="t('footer.buildIdTitle')"
      >
        {{ buildIdDisplay }}
      </p>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { LOCALE_PREF_KEY, readConsent } from '~/utils/privacy-consent'

const { t, locale, setLocale } = useI18n()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()

const buildIdDisplay = computed(() => {
  const id = String(runtimeConfig.public.buildId ?? '').trim()
  return id ? `Build · ${id}` : ''
})
const colorMode = useColorMode()
const { openCookieSettings } = useCookieConsent()

const themeSegIndex = computed(() => {
  const p = colorMode.preference
  if (p === 'light')
    return 0
  if (p === 'system')
    return 1
  return 2
})

const brandTheme = {
  light: '#1e40af',
  dark: '#0a101d',
} as const

const appConfig = useAppConfig()
const baseUrl = useWscSiteBase()

const jsonLdGraph = computed(() => {
  const base = baseUrl.value
  if (!base)
    return null

  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'WebSite',
        '@id': `${base}/#website`,
        url: base,
        name: 'De Huur Radar',
        inLanguage: ['nl-NL', 'en'],
        description: t('meta.description'),
        publisher: { '@id': `${base}/#organization` },
        potentialAction: {
          '@type': 'ReadAction',
          target: base,
        },
      },
      {
        '@type': 'Organization',
        '@id': `${base}/#organization`,
        name: 'De Huur Radar',
        url: base,
      },
      {
        '@type': 'SoftwareApplication',
        '@id': `${base}/#software`,
        name: 'De Huur Radar',
        applicationCategory: 'UtilitiesApplication',
        operatingSystem: 'Any',
        browserRequirements: 'Requires JavaScript. Requires a modern browser.',
        offers: { '@type': 'Offer', price: '0', priceCurrency: 'EUR' },
        url: base,
        description: t('meta.description'),
      },
    ],
  }
})

const themeColor = computed(() => {
  const b = appConfig.brand
  const light = b?.themeColorLight ?? brandTheme.light
  const dark = b?.themeColorDark ?? brandTheme.dark
  return colorMode.value === 'dark' ? dark : light
})

onMounted(() => {
  if (!import.meta.client)
    return
  const c = readConsent()
  if (!c?.functional)
    return
  try {
    const saved = localStorage.getItem(LOCALE_PREF_KEY)
    if (saved === 'nl' || saved === 'en')
      void setLocale(saved)
  }
  catch {
    /* ignore */
  }
})

watch(locale, (val) => {
  if (!import.meta.client)
    return
  const c = readConsent()
  if (c?.functional && (val === 'nl' || val === 'en')) {
    try {
      localStorage.setItem(LOCALE_PREF_KEY, val)
    }
    catch {
      /* ignore */
    }
  }
})

useHead(() => ({
  htmlAttrs: {
    lang: locale.value,
  },
  meta: [
    { name: 'theme-color', content: themeColor.value },
    { name: 'apple-mobile-web-app-title', content: t('brand.name') },
  ],
  link: [{ rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' }],
  script: jsonLdGraph.value
    ? [
        {
          type: 'application/ld+json',
          innerHTML: JSON.stringify(jsonLdGraph.value),
        },
      ]
    : [],
}))
</script>

<style scoped>
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.layout-root {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.layout-main {
  flex: 1 1 auto;
  min-width: 0;
}

.skip-link {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
  z-index: 100;
}

.skip-link:focus,
.skip-link:focus-visible {
  position: fixed;
  top: 0.65rem;
  left: 0.65rem;
  width: auto;
  height: auto;
  margin: 0;
  padding: 0.65rem 1rem;
  overflow: visible;
  clip: auto;
  white-space: normal;
  background: var(--accent);
  color: var(--surface-elevated);
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  outline: 2px solid var(--surface-elevated);
  outline-offset: 2px;
}

.footer__a11y {
  max-width: 960px;
  margin: 0 auto 0.75rem;
  font-size: 0.78rem;
  color: var(--text-tertiary);
  text-align: center;
  line-height: 1.5;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 40;
  background: color-mix(in srgb, var(--surface-elevated) 92%, transparent);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-subtle);
  box-shadow: var(--shadow-sm);
  animation: wsc-fade-in var(--duration-normal) var(--ease-out) backwards;
  transition:
    background 0.45s var(--ease-out),
    border-color 0.4s var(--ease-out),
    box-shadow 0.45s var(--ease-out);
}

.topbar__inner {
  max-width: 960px;
  margin: 0 auto;
  padding: 0.875rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.brand--link {
  text-decoration: none;
  color: inherit;
}

.brand--link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

.brand__mark {
  display: flex;
  flex-shrink: 0;
  transition: transform var(--duration-normal) var(--ease-spring);
}

@media (prefers-reduced-motion: no-preference) {
  .brand--link:hover .brand__mark {
    transform: scale(1.04);
  }
}

.brand__text {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  min-width: 0;
}

.brand__name {
  font-weight: 700;
  font-size: 1.05rem;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.brand__badge {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--accent);
}

.topbar__actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.topbar__faq {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: none;
  white-space: nowrap;
  padding: 0.4rem 0.55rem;
  border-radius: var(--radius-md);
  margin-right: 0.25rem;
  transition:
    color var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.topbar__faq:hover {
  text-decoration: none;
  color: var(--accent-hover);
  background: var(--accent-muted);
  box-shadow: 0 0 0 1px color-mix(in srgb, var(--accent) 18%, transparent);
}

.topbar__faq:active {
  transform: scale(0.98);
}

.seg {
  position: relative;
  display: inline-flex;
  align-items: stretch;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-subtle);
  background: var(--surface-muted);
  padding: 3px;
  gap: 2px;
}

.seg__pill {
  position: absolute;
  top: 3px;
  bottom: 3px;
  left: 3px;
  width: calc(50% - 3px);
  border-radius: calc(var(--radius-sm) - 2px);
  background: var(--surface-elevated);
  box-shadow:
    inset 0 0 0 1px var(--border-strong),
    0 1px 3px color-mix(in srgb, var(--text-primary) 8%, transparent);
  z-index: 0;
  pointer-events: none;
  transition:
    transform 0.44s var(--ease-spring),
    box-shadow 0.4s var(--ease-out);
  will-change: transform;
}

.seg--2.seg--i1 .seg__pill {
  transform: translateX(calc(100% + 2px));
}

.seg--3 .seg__pill {
  width: calc(33.333% - 2.5px);
}

.seg--3.seg--i1 .seg__pill {
  transform: translateX(calc(100% + 2px));
}

.seg--3.seg--i2 .seg__pill {
  transform: translateX(calc(200% + 4px));
}

.seg__btn {
  position: relative;
  z-index: 1;
  border: none;
  background: transparent;
  color: var(--text-secondary);
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.4rem 0.65rem;
  cursor: pointer;
  line-height: 1.2;
  flex: 1 1 0;
  min-width: 0;
  transition:
    color var(--duration-fast) var(--ease-out),
    transform 0.2s var(--ease-out);
}

.seg__btn--icon {
  flex: 1 1 0;
  min-width: 2.25rem;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.seg__glyph {
  display: inline-block;
  transition: transform 0.38s var(--ease-spring);
  transform-origin: center;
}

.seg__btn--active .seg__glyph {
  transform: scale(1.12);
}

.seg__btn:active:not(:disabled) {
  transform: scale(0.96);
}

.seg__btn:hover:not(.seg__btn--active) {
  color: var(--text-primary);
}

.seg__btn--active {
  color: var(--accent);
  font-weight: 700;
}

.seg__btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: calc(var(--radius-sm) - 2px);
}

.topbar__faq:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.footer__link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
}

@media (prefers-reduced-motion: reduce) {
  .seg__pill {
    transition: none;
  }

  .seg__btn--active .seg__glyph {
    transform: none;
  }
}

.footer {
  margin-top: auto;
  padding: 1.5rem 1.25rem 2rem;
  border-top: 1px solid var(--border-subtle);
  background: var(--surface-muted);
  animation: wsc-fade-in var(--duration-slow) var(--ease-out) 0.25s backwards;
}

.footer__site-links {
  margin: 0 0 0.75rem;
  font-size: 0.9rem;
}

.footer__site-link {
  font-weight: 600;
  color: var(--accent);
  text-decoration: none;
}

.footer__site-link:hover {
  color: var(--accent-hover);
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.footer__build {
  margin: 1rem 0 0;
  font-size: 0.65rem;
  line-height: 1.4;
  color: var(--text-tertiary);
  letter-spacing: 0.02em;
  word-break: break-all;
}

@media (prefers-reduced-motion: reduce) {
  .footer {
    animation: none;
  }
}

.footer__highlight {
  max-width: 960px;
  margin: 0 auto 0.65rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-align: center;
  line-height: 1.5;
}

.footer__text {
  max-width: 960px;
  margin: 0 auto 0.5rem;
  font-size: 0.8rem;
  color: var(--text-tertiary);
  text-align: center;
  line-height: 1.5;
}

.footer__credit {
  margin-bottom: 0.65rem;
}

.footer__credit-sep {
  margin: 0 0.45rem;
  opacity: 0.45;
}

.footer__inline-link {
  color: var(--accent);
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 0.15em;
  transition: color var(--duration-fast) var(--ease-out);
}

.footer__inline-link:hover {
  color: var(--accent-hover);
}

.footer__link {
  display: block;
  margin: 0.75rem auto 0;
  padding: 0.35rem 0.5rem;
  border: none;
  background: none;
  color: var(--accent);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.footer__link:hover {
  color: var(--accent-hover);
}
</style>
