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

        <nav class="topbar__actions topbar__actions--desktop" :aria-label="t('a11y.siteNav')">
          <div class="topbar__nav-cluster" role="presentation">
            <NuxtLink
              class="topbar__nav-link topbar__nav-link--primary"
              :to="localePath('/check')"
              active-class="topbar__nav-link--active"
            >
              {{ t('header.check') }}
            </NuxtLink>
            <NuxtLink
              class="topbar__nav-link"
              :to="localePath('/faq')"
              active-class="topbar__nav-link--active"
            >
              {{ t('header.faq') }}
            </NuxtLink>
          </div>
          <div
            class="seg seg--2 seg--lang"
            :class="locale === 'nl' ? 'seg--i0' : 'seg--i1'"
            role="group"
            :aria-label="t('header.language')"
          >
            <span class="seg__pill" aria-hidden="true" />
            <button
              type="button"
              class="seg__btn seg__btn--lang"
              :class="{ 'seg__btn--active': locale === 'nl' }"
              :aria-pressed="locale === 'nl'"
              :aria-label="t('header.langNl')"
              @click="setLocale('nl')"
            >
              {{ t('header.langNlShort') }}
            </button>
            <button
              type="button"
              class="seg__btn seg__btn--lang"
              :class="{ 'seg__btn--active': locale === 'en' }"
              :aria-pressed="locale === 'en'"
              :aria-label="t('header.langEn')"
              @click="setLocale('en')"
            >
              {{ t('header.langEnShort') }}
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

        <button
          type="button"
          class="topbar__menu-btn"
          :aria-expanded="mobileMenuOpen"
          :aria-controls="mobileMenuPanelId"
          :aria-label="mobileMenuOpen ? t('header.menuClose') : t('header.menuOpen')"
          @click="toggleMobileMenu"
        >
          <span class="topbar__menu-bars" aria-hidden="true">
            <span />
            <span />
            <span />
          </span>
        </button>
      </div>
    </header>

    <Teleport to="body">
      <Transition name="wsc-mobile-nav">
        <div
          v-if="mobileMenuOpen"
          :id="mobileMenuPanelId"
          class="mobile-nav"
          role="dialog"
          aria-modal="true"
          tabindex="-1"
          :aria-label="t('header.menuTitle')"
          @keydown.escape.prevent="closeMobileMenu"
        >
          <div
            class="mobile-nav__backdrop"
            aria-hidden="true"
            @click="closeMobileMenu"
          />
          <div class="mobile-nav__sheet">
            <div class="mobile-nav__head">
              <p class="mobile-nav__title font-display">{{ t('header.menuTitle') }}</p>
              <button
                type="button"
                class="mobile-nav__close"
                :aria-label="t('header.menuClose')"
                @click="closeMobileMenu"
              >
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="mobile-nav__scroll">
              <p class="mobile-nav__section-label">{{ t('header.menuSectionPages') }}</p>
              <nav class="mobile-nav__links" :aria-label="t('header.menuSectionPages')">
                <NuxtLink
                  class="mobile-nav__link"
                  :to="localePath('/check')"
                  active-class="mobile-nav__link--active"
                  @click="closeMobileMenu"
                >
                  {{ t('header.check') }}
                </NuxtLink>
                <NuxtLink
                  class="mobile-nav__link"
                  :to="localePath('/faq')"
                  active-class="mobile-nav__link--active"
                  @click="closeMobileMenu"
                >
                  {{ t('header.faq') }}
                </NuxtLink>
              </nav>

              <p class="mobile-nav__section-label">{{ t('header.menuSectionLanguage') }}</p>
              <div
                class="seg seg--2 seg--lang mobile-nav__seg"
                :class="locale === 'nl' ? 'seg--i0' : 'seg--i1'"
                role="group"
                :aria-label="t('header.language')"
              >
                <span class="seg__pill" aria-hidden="true" />
                <button
                  type="button"
                  class="seg__btn seg__btn--lang"
                  :class="{ 'seg__btn--active': locale === 'nl' }"
                  :aria-pressed="locale === 'nl'"
                  :aria-label="t('header.langNl')"
                  @click="setLocale('nl')"
                >
                  {{ t('header.langNlShort') }}
                </button>
                <button
                  type="button"
                  class="seg__btn seg__btn--lang"
                  :class="{ 'seg__btn--active': locale === 'en' }"
                  :aria-pressed="locale === 'en'"
                  :aria-label="t('header.langEn')"
                  @click="setLocale('en')"
                >
                  {{ t('header.langEnShort') }}
                </button>
              </div>

              <p class="mobile-nav__section-label">{{ t('header.menuSectionAppearance') }}</p>
              <div
                class="seg seg--3 mobile-nav__seg"
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
                  @click="colorMode.preference = 'dark'"
                >
                  <span class="seg__glyph" aria-hidden="true">☾</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <main id="main-content" class="layout-main" tabindex="-1">
      <slot />
    </main>

    <button
      v-show="showBackToTop && !cookieDialogOpen"
      type="button"
      class="back-to-top"
      :aria-label="t('a11y.backToTop')"
      @click="scrollToTop"
    >
      <span class="back-to-top__icon" aria-hidden="true">↑</span>
      <span class="back-to-top__text">{{ t('a11y.backToTopShort') }}</span>
    </button>

    <footer class="footer">
      <div class="footer__inner">
        <div class="footer__top">
          <nav class="footer__nav" :aria-label="t('footer.navAria')">
            <NuxtLink class="footer__nav-link" :to="localePath('/check')">{{ t('header.check') }}</NuxtLink>
            <span class="footer__nav-sep" aria-hidden="true" />
            <NuxtLink class="footer__nav-link" :to="localePath('/faq')">{{ t('header.faq') }}</NuxtLink>
          </nav>
          <button type="button" class="footer__cookie" @click="openCookieSettings">
            {{ t('cookies.openSettings') }}
          </button>
        </div>

        <div class="footer__privacy">
          <p class="footer__privacy-text">{{ t('cookies.footerSummary') }}</p>
        </div>

        <div class="footer__fineprint">
          <p class="footer__fineprint-line" role="note">{{ t('a11y.footerStatement') }}</p>
          <p class="footer__fineprint-line">{{ t('footer.note') }}</p>
        </div>

        <p class="footer__credit">
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

        <p
          v-if="buildIdDisplay && showBuildFooter"
          class="footer__build"
          :title="t('footer.buildIdTitle')"
        >
          {{ buildIdDisplay }}
        </p>
      </div>
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

/** Alleen in development: productie-footer zonder technische build-regel. */
const showBuildFooter = import.meta.dev
const colorMode = useColorMode()
const { openCookieSettings, showDialog: cookieDialogOpen } = useCookieConsent()

const mobileMenuPanelId = 'wsc-mobile-nav-panel'
const mobileMenuOpen = ref(false)
const route = useRoute()

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

watch(() => route.fullPath, () => {
  closeMobileMenu()
})

watch(cookieDialogOpen, (open) => {
  if (open)
    closeMobileMenu()
})

watch(mobileMenuOpen, async (open) => {
  if (!import.meta.client)
    return
  document.body.style.overflow = open ? 'hidden' : ''
  if (open) {
    await nextTick()
    document.getElementById(mobileMenuPanelId)?.focus({ preventScroll: true })
  }
})

const scrollY = ref(0)
const showBackToTop = computed(() => scrollY.value > 280)

function onWindowScroll() {
  if (!import.meta.client)
    return
  scrollY.value = window.scrollY || document.documentElement.scrollTop || 0
}

function scrollToTop() {
  if (!import.meta.client)
    return
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' })
  document.getElementById('main-content')?.focus({ preventScroll: true })
}

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
        description: t('meta.homeDescription'),
        publisher: { '@id': `${base}/#organization` },
        potentialAction: {
          '@type': 'ReadAction',
          target: `${base}/check`,
        },
      },
      {
        '@type': 'Organization',
        '@id': `${base}/#organization`,
        name: 'De Huur Radar',
        url: base,
        logo: { '@type': 'ImageObject', url: `${base}/og.png` },
      },
      {
        '@type': 'SoftwareApplication',
        '@id': `${base}/#software`,
        name: 'De Huur Radar',
        applicationCategory: 'UtilitiesApplication',
        operatingSystem: 'Any',
        browserRequirements: 'Requires JavaScript. Requires a modern browser.',
        offers: { '@type': 'Offer', price: '0', priceCurrency: 'EUR' },
        url: `${base}/check`,
        description: t('meta.homeDescription'),
        image: `${base}/og.png`,
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

let mqDesktopListener: (() => void) | null = null

onMounted(() => {
  if (!import.meta.client)
    return
  onWindowScroll()
  window.addEventListener('scroll', onWindowScroll, { passive: true })
  const c = readConsent()
  if (c?.functional) {
    try {
      const saved = localStorage.getItem(LOCALE_PREF_KEY)
      if (saved === 'nl' || saved === 'en')
        void setLocale(saved)
    }
    catch {
      /* ignore */
    }
  }
  const mq = window.matchMedia('(min-width: 768px)')
  mqDesktopListener = () => {
    if (mq.matches)
      mobileMenuOpen.value = false
  }
  mq.addEventListener('change', mqDesktopListener)
})

onUnmounted(() => {
  if (!import.meta.client)
    return
  window.removeEventListener('scroll', onWindowScroll)
  document.body.style.overflow = ''
  if (mqDesktopListener) {
    const mq = window.matchMedia('(min-width: 768px)')
    mq.removeEventListener('change', mqDesktopListener)
    mqDesktopListener = null
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
  padding-left: env(safe-area-inset-left, 0px);
  padding-right: env(safe-area-inset-right, 0px);
  padding-bottom: env(safe-area-inset-bottom, 0px);
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

.topbar {
  position: sticky;
  top: 0;
  /* Boven cookiemodal (backdrop ~200) zodat taal/thema op mobiel blijven werken */
  z-index: 250;
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
  padding-top: max(0.75rem, env(safe-area-inset-top, 0px));
  padding-right: max(1.25rem, env(safe-area-inset-right, 0px));
  padding-bottom: 0.875rem;
  padding-left: max(1.25rem, env(safe-area-inset-left, 0px));
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: nowrap;
}

.topbar__actions--desktop {
  display: none;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

@media (min-width: 768px) {
  .topbar__actions--desktop {
    display: flex;
  }
}

.topbar__menu-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  min-width: 48px;
  min-height: 48px;
  margin-left: auto;
  padding: 0;
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  background: var(--surface-muted);
  color: var(--text-primary);
  cursor: pointer;
  touch-action: manipulation;
  box-shadow: var(--shadow-sm);
  transition:
    background var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.topbar__menu-btn:hover {
  border-color: color-mix(in srgb, var(--accent) 22%, var(--border-subtle));
  background: color-mix(in srgb, var(--accent) 6%, var(--surface-muted));
}

.topbar__menu-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.topbar__menu-btn:active {
  transform: scale(0.97);
}

.topbar__menu-bars {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 1.35rem;
}

.topbar__menu-bars span {
  display: block;
  height: 2px;
  border-radius: 1px;
  background: var(--text-primary);
}

@media (min-width: 768px) {
  .topbar__menu-btn {
    display: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .topbar__menu-btn:active {
    transform: none;
  }
}

.mobile-nav {
  position: fixed;
  inset: 0;
  z-index: 255;
  display: flex;
  justify-content: flex-end;
  pointer-events: auto;
}

.mobile-nav__backdrop {
  position: absolute;
  inset: 0;
  background: color-mix(in srgb, var(--text-primary) 42%, transparent);
  backdrop-filter: blur(3px);
}

.mobile-nav__sheet {
  position: relative;
  z-index: 1;
  width: min(20.5rem, 92vw);
  height: 100%;
  max-height: 100dvh;
  background: var(--surface-elevated);
  border-left: 1px solid var(--border-subtle);
  box-shadow: -8px 0 40px color-mix(in srgb, var(--text-primary) 12%, transparent);
  display: flex;
  flex-direction: column;
  padding-bottom: env(safe-area-inset-bottom, 0px);
}

.mobile-nav__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: max(0.75rem, env(safe-area-inset-top, 0px)) 0.65rem 0.65rem 1rem;
  border-bottom: 1px solid var(--border-subtle);
  flex-shrink: 0;
}

.mobile-nav__title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.mobile-nav__close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 48px;
  min-height: 48px;
  padding: 0;
  border: none;
  border-radius: var(--radius-md);
  background: transparent;
  color: var(--text-secondary);
  font-size: 1.65rem;
  line-height: 1;
  cursor: pointer;
  touch-action: manipulation;
}

.mobile-nav__close:hover {
  color: var(--text-primary);
  background: var(--surface-muted);
}

.mobile-nav__close:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.mobile-nav__scroll {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 0.85rem 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mobile-nav__section-label {
  margin: 0.5rem 0 0.1rem;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-tertiary);
}

.mobile-nav__section-label:first-child {
  margin-top: 0;
}

.mobile-nav__links {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.mobile-nav__link {
  display: flex;
  align-items: center;
  min-height: 48px;
  padding: 0.45rem 0.85rem;
  border-radius: var(--radius-md);
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text-primary);
  text-decoration: none;
  transition:
    background var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out);
}

.mobile-nav__link:hover {
  background: var(--surface-muted);
}

.mobile-nav__link--active {
  background: var(--accent-muted);
  color: var(--accent);
}

.mobile-nav__seg {
  align-self: stretch;
  width: 100%;
  max-width: 100%;
}

.wsc-mobile-nav-enter-active,
.wsc-mobile-nav-leave-active {
  transition: opacity 0.28s var(--ease-out);
}

.wsc-mobile-nav-enter-active .mobile-nav__sheet,
.wsc-mobile-nav-leave-active .mobile-nav__sheet {
  transition: transform 0.32s var(--ease-out);
}

.wsc-mobile-nav-enter-from,
.wsc-mobile-nav-leave-to {
  opacity: 0;
}

.wsc-mobile-nav-enter-from .mobile-nav__sheet,
.wsc-mobile-nav-leave-to .mobile-nav__sheet {
  transform: translateX(100%);
}

@media (prefers-reduced-motion: reduce) {
  .wsc-mobile-nav-enter-active .mobile-nav__sheet,
  .wsc-mobile-nav-leave-active .mobile-nav__sheet {
    transition: none;
  }

  .wsc-mobile-nav-enter-from .mobile-nav__sheet,
  .wsc-mobile-nav-leave-to .mobile-nav__sheet {
    transform: none;
  }
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

.topbar__nav-cluster {
  display: inline-flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.1rem;
  padding: 0.2rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: color-mix(in srgb, var(--surface-muted) 88%, var(--surface-page));
  box-shadow: var(--shadow-sm);
}

.topbar__nav-link {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-decoration: none;
  white-space: nowrap;
  padding: 0.38rem 0.6rem;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  border-radius: calc(var(--radius-md) - 2px);
  touch-action: manipulation;
  transition:
    color var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

@media (hover: hover) and (pointer: fine) {
  .topbar__nav-link:hover {
    text-decoration: none;
    color: var(--accent);
    background: color-mix(in srgb, var(--accent-muted) 55%, transparent);
  }
}

.topbar__nav-link--primary {
  color: var(--accent);
  font-weight: 700;
}

.topbar__nav-link--active {
  color: var(--accent);
  background: var(--surface-elevated);
  box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--accent) 24%, var(--border-subtle));
}

.topbar__nav-link--active:hover {
  color: var(--accent-hover);
  background: var(--surface-elevated);
}

.topbar__nav-link:active {
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
  min-height: 40px;
  cursor: pointer;
  line-height: 1.2;
  flex: 1 1 0;
  min-width: 0;
  touch-action: manipulation;
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

@media (hover: hover) and (pointer: fine) {
  .seg__btn:hover:not(.seg__btn--active) {
    color: var(--text-primary);
  }
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

.topbar__nav-link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.seg--lang {
  flex-shrink: 0;
}

.seg__btn--lang {
  font-size: 0.7rem;
  padding: 0.38rem 0.48rem;
  letter-spacing: -0.02em;
}

.back-to-top {
  position: fixed;
  right: max(0.85rem, env(safe-area-inset-right, 0px));
  bottom: max(0.85rem, env(safe-area-inset-bottom, 0px));
  z-index: 240;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  padding: 0.5rem 0.85rem;
  min-height: 48px;
  font: inherit;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: #fff;
  background: var(--accent);
  border: none;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  cursor: pointer;
  touch-action: manipulation;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

.back-to-top:hover {
  background: var(--accent-hover);
}

.back-to-top:active {
  transform: scale(0.98);
}

.back-to-top:focus-visible {
  outline: 2px solid var(--surface-elevated);
  outline-offset: 2px;
  box-shadow: 0 0 0 4px var(--accent-muted);
}

.back-to-top__icon {
  font-size: 1rem;
  line-height: 1;
}

@media (max-width: 380px) {
  .back-to-top__text {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  .back-to-top {
    width: 48px;
    height: 48px;
    padding: 0;
    border-radius: 50%;
  }
}

@media (prefers-reduced-motion: reduce) {
  .back-to-top:active {
    transform: none;
  }
}

.footer__cookie:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
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
  border-top: 1px solid var(--border-subtle);
  background: linear-gradient(
    180deg,
    color-mix(in srgb, var(--surface-page) 40%, var(--surface-muted)) 0%,
    var(--surface-muted) 100%
  );
  animation: wsc-fade-in var(--duration-slow) var(--ease-out) 0.25s backwards;
}

.footer__inner {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.75rem 1.25rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.footer__top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.65rem 1rem;
}

.footer__nav {
  display: inline-flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.35rem 0.65rem;
}

.footer__nav-link {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: none;
  padding: 0.35rem 0.5rem;
  border-radius: var(--radius-md);
  transition:
    color var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out);
}

.footer__nav-link:hover {
  color: var(--accent-hover);
  background: var(--accent-muted);
}

.footer__nav-link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.footer__nav-sep {
  width: 1px;
  height: 0.85rem;
  background: color-mix(in srgb, var(--border-subtle) 80%, var(--text-tertiary));
  border-radius: 1px;
  flex-shrink: 0;
}

.footer__cookie {
  font: inherit;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0.4rem 0.85rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: var(--surface-elevated);
  box-shadow: var(--shadow-sm);
  transition:
    color var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

.footer__cookie:hover {
  color: var(--accent);
  border-color: color-mix(in srgb, var(--accent) 22%, var(--border-subtle));
  background: color-mix(in srgb, var(--accent) 6%, var(--surface-elevated));
}

.footer__privacy {
  padding: 0.85rem 1rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-subtle);
  background: var(--surface-elevated);
  box-shadow: var(--shadow-sm);
}

.footer__privacy-text {
  margin: 0;
  max-width: 40rem;
  margin-inline: auto;
  font-size: 0.8125rem;
  font-weight: 600;
  line-height: 1.5;
  color: var(--text-secondary);
  text-align: center;
}

.footer__fineprint {
  max-width: 38rem;
  margin-inline: auto;
  padding: 0 0.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.footer__fineprint-line {
  margin: 0;
  font-size: 0.75rem;
  line-height: 1.55;
  color: var(--text-tertiary);
  text-align: center;
}

.footer__credit {
  margin: 0;
  font-size: 0.8125rem;
  line-height: 1.55;
  color: var(--text-secondary);
  text-align: center;
}

.footer__credit-sep {
  margin: 0 0.4rem;
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

.footer__build {
  margin: 0;
  padding-top: 0.25rem;
  font-size: 0.65rem;
  line-height: 1.45;
  font-variant-numeric: tabular-nums;
  color: var(--text-tertiary);
  letter-spacing: 0.02em;
  text-align: center;
  word-break: break-all;
  opacity: 0.85;
}

@media (prefers-reduced-motion: reduce) {
  .footer {
    animation: none;
  }
}
</style>
