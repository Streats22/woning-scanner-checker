<template>
  <div class="layout-root">
    <a href="#main-content" class="skip-link">{{ t('a11y.skipToContent') }}</a>
    <header class="topbar">
      <div class="topbar__inner wsc-container">
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
              class="topbar__nav-link"
              :to="localePath('/')"
              exact-active-class="topbar__nav-link--active"
            >
              {{ t('header.home') }}
            </NuxtLink>
            <NuxtLink
              class="topbar__nav-link topbar__nav-link--primary"
              :to="localePath('/check')"
              active-class="topbar__nav-link--active"
            >
              {{ t('header.check') }}
            </NuxtLink>
            <NuxtLink
              class="topbar__nav-link"
              :to="localePath('/huursites')"
              active-class="topbar__nav-link--active"
            >
              {{ t('header.sites') }}
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
            :class="['seg', showThemeSystemOption ? 'seg--3' : 'seg--2', 'seg--i' + themeSegIndex]"
            role="group"
            :aria-label="t('header.menuSectionAppearance')"
          >
            <span class="seg__pill" aria-hidden="true" />
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': themeLightActive }"
              :aria-pressed="themeLightActive"
              :aria-label="t('header.themeLight')"
              :title="t('header.themeLight')"
              @click="colorMode.preference = 'light'"
            >
              <span class="seg__glyph seg__glyph--icon" aria-hidden="true">☀</span>
            </button>
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': themeDarkActive }"
              :aria-pressed="themeDarkActive"
              :aria-label="t('header.themeDark')"
              :title="t('header.themeDark')"
              @click="colorMode.preference = 'dark'"
            >
              <span class="seg__glyph seg__glyph--icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.15em" height="1.15em" aria-hidden="true">
                  <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                </svg>
              </span>
            </button>
            <button
              v-if="showThemeSystemOption"
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'system' }"
              :aria-pressed="colorMode.preference === 'system'"
              :aria-label="t('header.themeSystem')"
              :title="t('header.themeSystem')"
              @click="colorMode.preference = 'system'"
            >
              <span class="seg__glyph seg__glyph--icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.1em" height="1.1em" aria-hidden="true">
                  <path
                    d="M2 5.5A2.5 2.5 0 0 1 4.5 3h15A2.5 2.5 0 0 1 22 5.5v9a2.5 2.5 0 0 1-2.5 2.5h-4.1l.2 1h1.4a.75.75 0 0 1 0 1.5H7a.75.75 0 0 1 0-1.5h1.4l.2-1H4.5A2.5 2.5 0 0 1 2 14.5v-9Zm2.5-1A1 1 0 0 0 4 5.5v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1h-14Z"
                  />
                </svg>
              </span>
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
                  :to="localePath('/')"
                  exact-active-class="mobile-nav__link--active"
                  @click="closeMobileMenu"
                >
                  {{ t('header.home') }}
                </NuxtLink>
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
                  :to="localePath('/huursites')"
                  active-class="mobile-nav__link--active"
                  @click="closeMobileMenu"
                >
                  {{ t('header.sites') }}
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
                :class="['seg', 'mobile-nav__seg', showThemeSystemOption ? 'seg--3' : 'seg--2', 'seg--i' + themeSegIndex]"
                role="group"
                :aria-label="t('header.menuSectionAppearance')"
              >
                <span class="seg__pill" aria-hidden="true" />
                <button
                  type="button"
                  class="seg__btn seg__btn--icon"
                  :class="{ 'seg__btn--active': themeLightActive }"
                  :aria-pressed="themeLightActive"
                  :aria-label="t('header.themeLight')"
                  @click="colorMode.preference = 'light'"
                >
                  <span class="seg__glyph seg__glyph--icon" aria-hidden="true">☀</span>
                </button>
                <button
                  type="button"
                  class="seg__btn seg__btn--icon"
                  :class="{ 'seg__btn--active': themeDarkActive }"
                  :aria-pressed="themeDarkActive"
                  :aria-label="t('header.themeDark')"
                  @click="colorMode.preference = 'dark'"
                >
                  <span class="seg__glyph seg__glyph--icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.15em" height="1.15em" aria-hidden="true">
                      <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                    </svg>
                  </span>
                </button>
                <button
                  v-if="showThemeSystemOption"
                  type="button"
                  class="seg__btn seg__btn--icon"
                  :class="{ 'seg__btn--active': colorMode.preference === 'system' }"
                  :aria-pressed="colorMode.preference === 'system'"
                  :aria-label="t('header.themeSystem')"
                  @click="colorMode.preference = 'system'"
                >
                  <span class="seg__glyph seg__glyph--icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.1em" height="1.1em" aria-hidden="true">
                      <path
                        d="M2 5.5A2.5 2.5 0 0 1 4.5 3h15A2.5 2.5 0 0 1 22 5.5v9a2.5 2.5 0 0 1-2.5 2.5h-4.1l.2 1h1.4a.75.75 0 0 1 0 1.5H7a.75.75 0 0 1 0-1.5h1.4l.2-1H4.5A2.5 2.5 0 0 1 2 14.5v-9Zm2.5-1A1 1 0 0 0 4 5.5v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1h-14Z"
                      />
                    </svg>
                  </span>
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

    <Transition name="wsc-fab">
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
    </Transition>

    <footer class="footer">
      <div class="footer__inner wsc-container">
        <div class="footer__grid">
          <div class="footer__about">
            <NuxtLink :to="localePath('/')" class="footer__brand" :aria-label="t('header.navHome')">
              <BrandMark :size="36" decorative />
              <span class="footer__brand-name">{{ t('brand.name') }}</span>
            </NuxtLink>
            <p class="footer__note">{{ t('footer.note') }}</p>
          </div>

          <nav class="footer__nav" :aria-label="t('footer.navAria')">
            <NuxtLink class="footer__nav-link" :to="localePath('/')">{{ t('header.home') }}</NuxtLink>
            <NuxtLink class="footer__nav-link" :to="localePath('/check')">{{ t('header.check') }}</NuxtLink>
            <NuxtLink class="footer__nav-link" :to="localePath('/huursites')">{{ t('header.sites') }}</NuxtLink>
            <NuxtLink class="footer__nav-link" :to="localePath('/faq')">{{ t('header.faq') }}</NuxtLink>
            <button type="button" class="footer__nav-link footer__cookie" @click="openCookieSettings">
              {{ t('cookies.openSettings') }}
            </button>
          </nav>

          <div class="footer__meta">
            <p class="footer__credit">
              {{ t('footer.madeBy') }}
              <a
                class="footer__inline-link"
                href="https://streatsdesign.nl"
                target="_blank"
                rel="noopener"
              >streatsdesign.nl</a>
            </p>
            <p class="footer__credit">
              {{ t('footer.contactLabel') }}
              <a
                class="footer__inline-link"
                href="mailto:info@streatsdesign.nl"
              >info@streatsdesign.nl</a>
            </p>
          </div>
        </div>

        <div class="footer__fineprint">
          <p class="footer__fineprint-line">{{ t('cookies.footerSummary') }}</p>
          <p class="footer__fineprint-line" role="note">{{ t('a11y.footerStatement') }}</p>
          <p class="footer__fineprint-line">{{ t('footer.staleCacheHint') }}</p>
        </div>

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
import { LOCALE_PREF_KEY } from '~/utils/privacy-consent'

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
const { themeDisplay } = useWscThemeDisplay()
const { openCookieSettings, showDialog: cookieDialogOpen, needsChoice } = useCookieConsent()

/**
 * Derde optie (systeem) pas na mount: zo matcht de eerste client-render met SSR (twee knoppen)
 * en vermijden we hydratatieverschil wanneer localStorage al een cookiekeuze had.
 */
const themeExtraControlsMounted = ref(false)
/** Derde weergave-optie (systeem) pas zodra de gebruiker de cookiemelding heeft beantwoord. */
const showThemeSystemOption = computed(() => themeExtraControlsMounted.value && !needsChoice.value)

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

/**
 * Pills: voor cookiekeuze alleen licht/donker; daarna + systeem.
 * Bij voorkeur `system` in 2-koloms weergave volgt de pill de actuele `themeDisplay`.
 */
const themeSegIndex = computed(() => {
  if (showThemeSystemOption.value) {
    const p = colorMode.preference
    if (p === 'system')
      return 2
    if (p === 'dark')
      return 1
    return 0
  }
  if (colorMode.preference === 'system')
    return themeDisplay.value === 'dark' ? 1 : 0
  return colorMode.preference === 'dark' ? 1 : 0
})

const themeLightActive = computed(() => {
  if (showThemeSystemOption.value)
    return colorMode.preference === 'light'
  return colorMode.preference === 'light'
    || (colorMode.preference === 'system' && themeDisplay.value === 'light')
})

const themeDarkActive = computed(() => {
  if (showThemeSystemOption.value)
    return colorMode.preference === 'dark'
  return colorMode.preference === 'dark'
    || (colorMode.preference === 'system' && themeDisplay.value === 'dark')
})

const brandTheme = {
  light: '#0e3b2e',
  dark: '#0e3b2e',
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
        alternateName: ['Huur Radar', 'Huur scam check', 'Huurfraude-check'],
        inLanguage: ['nl-NL', 'en'],
        description: t('meta.homeDescription'),
        keywords: 'huren, huur, huur scam, huurfraude, huurwoning, kamer huren, veilig huren, betrouwbare huurwebsites',
        publisher: { '@id': `${base}/#organization` },
        creator: { '@id': 'https://streatsdesign.nl/#organization' },
        about: [
          { '@type': 'Thing', name: 'Huurfraude' },
          { '@type': 'Thing', name: 'Huur scam' },
          { '@type': 'Thing', name: 'Veilig huren in Nederland' },
        ],
        audience: {
          '@type': 'Audience',
          audienceType: 'Huurders, studenten en kamerzoekers in Nederland',
        },
        potentialAction: [
          {
            '@type': 'ReadAction',
            target: `${base}/check`,
          },
          {
            '@type': 'SearchAction',
            target: `${base}/check?text={query}`,
            'query-input': 'required name=query',
          },
        ],
      },
      {
        '@type': 'Organization',
        '@id': `${base}/#organization`,
        name: 'De Huur Radar',
        url: base,
        logo: { '@type': 'ImageObject', url: `${base}/icon-512.png`, width: 512, height: 512 },
        image: `${base}/og.png`,
        email: 'info@streatsdesign.nl',
        contactPoint: {
          '@type': 'ContactPoint',
          contactType: 'customer support',
          email: 'info@streatsdesign.nl',
          availableLanguage: ['Dutch', 'English'],
        },
      },
      {
        '@type': 'Organization',
        '@id': 'https://streatsdesign.nl/#organization',
        name: 'streatsdesign.nl',
        url: 'https://streatsdesign.nl',
      },
      {
        '@type': 'SoftwareApplication',
        '@id': `${base}/#software`,
        name: 'De Huur Radar — Huur scam check',
        alternateName: 'Huurfraude-check',
        applicationCategory: 'UtilitiesApplication',
        operatingSystem: 'Any',
        browserRequirements: 'Requires JavaScript. Requires a modern browser.',
        offers: { '@type': 'Offer', price: '0', priceCurrency: 'EUR' },
        url: `${base}/check`,
        description: t('meta.homeDescription'),
        keywords: 'huur scam, huurfraude, huren, huurwoning controleren, veilig huren',
        image: `${base}/og.png`,
      },
    ],
  }
})

const themeColor = computed(() => {
  const b = appConfig.brand
  const light = b?.themeColorLight ?? brandTheme.light
  const dark = b?.themeColorDark ?? brandTheme.dark
  return themeDisplay.value === 'dark' ? dark : light
})

let mqDesktopListener: (() => void) | null = null

onMounted(() => {
  if (!import.meta.client)
    return
  themeExtraControlsMounted.value = true
  onWindowScroll()
  window.addEventListener('scroll', onWindowScroll, { passive: true })
  if (needsChoice.value && colorMode.preference === 'system')
    colorMode.preference = 'light'
  try {
    const saved = localStorage.getItem(LOCALE_PREF_KEY)
    if (saved === 'nl' || saved === 'en')
      void setLocale(saved)
  }
  catch {
    /* ignore */
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
  if (val === 'nl' || val === 'en') {
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
  link: [
    { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
    { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
  ],
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
  z-index: 300;
}

.skip-link:focus,
.skip-link:focus-visible {
  position: fixed;
  top: 0.65rem;
  left: 0.65rem;
  width: auto;
  height: auto;
  margin: 0;
  padding: 0.75rem 1.1rem;
  overflow: visible;
  clip: auto;
  white-space: normal;
  background: var(--action);
  color: var(--on-action);
  font-weight: 700;
  font-size: 0.95rem;
  text-decoration: none;
  border-radius: var(--radius-md);
  outline: 3px solid var(--brand);
  outline-offset: 2px;
}

/* ── Header: woudgroene balk, loopt naadloos over in de paginakop (.wsc-hero) ── */
.topbar {
  position: sticky;
  top: 0;
  /* Boven cookiemodal (backdrop ~200) zodat taal/thema op mobiel blijven werken */
  z-index: 250;
  background: var(--brand);
  color: var(--on-brand);
}

.topbar__inner {
  padding-top: max(0.85rem, env(safe-area-inset-top, 0px));
  padding-bottom: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: nowrap;
}

.topbar__actions--desktop {
  display: none;
  align-items: center;
  gap: 0.5rem;
}

@media (min-width: 860px) {
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
  border: 0;
  border-radius: var(--radius-md);
  background: var(--on-brand-line);
  color: var(--on-brand);
  cursor: pointer;
  touch-action: manipulation;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.topbar__menu-btn:hover {
  background: rgba(200, 242, 220, 0.24);
}

.topbar__menu-btn:active {
  transform: scale(0.94);
}

/* Radar-logo: de wijzer maakt een rondje bij hover */
.brand--link :deep(.brand-mark__lines) {
  transform-origin: 20px 20px;
  transition: transform 0.7s var(--ease-out);
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .brand--link:hover :deep(.brand-mark__lines) {
    transform: rotate(360deg);
  }
}

/* Knop "Terug naar boven" verschijnt en verdwijnt zacht */
.wsc-fab-enter-active,
.wsc-fab-leave-active {
  transition:
    opacity var(--duration-normal) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

.wsc-fab-enter-from,
.wsc-fab-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.92);
}

@media (prefers-reduced-motion: reduce) {
  .wsc-fab-enter-from,
  .wsc-fab-leave-to {
    transform: none;
  }
}

.topbar__menu-btn:focus-visible {
  outline: 3px solid var(--brand-soft);
  outline-offset: 2px;
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
  height: 2.5px;
  border-radius: 2px;
  background: var(--on-brand);
}

.topbar__menu-bars span:last-child {
  width: 65%;
}

@media (min-width: 860px) {
  .topbar__menu-btn {
    display: none;
  }
}

/* ── Mobiel menu: volledig groen vlak met grote links ── */
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
  background: rgba(6, 20, 15, 0.55);
}

.mobile-nav__sheet {
  position: relative;
  z-index: 1;
  width: min(26rem, 100vw);
  height: 100%;
  max-height: 100dvh;
  background: var(--brand);
  color: var(--on-brand);
  display: flex;
  flex-direction: column;
  padding-bottom: env(safe-area-inset-bottom, 0px);
}

.mobile-nav__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: max(0.85rem, env(safe-area-inset-top, 0px)) 1.25rem 0.5rem;
  flex-shrink: 0;
}

.mobile-nav__title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--on-brand-muted);
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
  background: var(--brand-soft);
  color: var(--brand);
  font-size: 1.75rem;
  line-height: 1;
  cursor: pointer;
  touch-action: manipulation;
}

.mobile-nav__close:focus-visible {
  outline: 3px solid var(--on-brand);
  outline-offset: 2px;
}

.mobile-nav__scroll {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 0.5rem 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.mobile-nav__section-label {
  margin: 1rem 0 0.1rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--on-brand-muted);
}

.mobile-nav__section-label:first-child {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
}

.mobile-nav__links {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.mobile-nav__link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-height: 56px;
  padding: 0.2rem 0;
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.035em;
  line-height: 1.1;
  color: var(--on-brand);
  text-decoration: none;
}

.mobile-nav__link:hover {
  color: var(--brand-soft);
  text-decoration: none;
  transform: none;
}

.mobile-nav__link--active::after {
  content: '';
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--action);
}

.mobile-nav__link:focus-visible {
  outline: 3px solid var(--brand-soft);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
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

/* ── Merk ── */
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

.brand--link:hover {
  text-decoration: none;
  color: inherit;
}

.brand--link:focus-visible {
  outline: 3px solid var(--brand-soft);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

.brand__mark {
  display: flex;
  flex-shrink: 0;
}

.brand__text {
  display: flex;
  flex-direction: column;
  gap: 0.05rem;
  min-width: 0;
}

.brand__name {
  font-weight: 800;
  font-size: 1.2rem;
  letter-spacing: -0.03em;
  line-height: 1.15;
  color: var(--on-brand);
}

.brand__badge {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--on-brand-muted);
}

/* ── Navigatie desktop ── */
.topbar__nav-cluster {
  display: inline-flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.15rem;
}

.topbar__nav-link {
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--on-brand-muted);
  text-decoration: none;
  white-space: nowrap;
  padding: 0.4rem 0.8rem;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  border-radius: var(--radius-sm);
  touch-action: manipulation;
  transition:
    color var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out);
}

.topbar__nav-link:hover {
  text-decoration: none;
  color: var(--on-brand);
  background: var(--on-brand-line);
  transform: none;
}

.topbar__nav-link--active {
  color: var(--on-brand);
  font-weight: 700;
}

/* “Analyse” als mint knop; actieve staat = wit */
.topbar__nav-link--primary {
  order: 10;
  margin-left: 0.35rem;
  padding: 0.4rem 1.1rem;
  background: var(--brand-soft);
  color: var(--brand);
  font-weight: 700;
  border-radius: var(--radius-sm);
}

.topbar__nav-link--primary:hover {
  background: #ffffff;
  color: var(--brand);
}

.topbar__nav-link--primary.topbar__nav-link--active {
  background: #ffffff;
  color: var(--brand);
}

.topbar__nav-link:focus-visible {
  outline: 3px solid var(--brand-soft);
  outline-offset: 2px;
}

/* ── Segmentknoppen (taal / weergave) op groen ── */
.seg {
  position: relative;
  display: inline-flex;
  align-items: stretch;
  border-radius: var(--radius-sm);
  background: var(--on-brand-line);
  padding: 3px;
  gap: 2px;
}

.seg__pill {
  position: absolute;
  top: 3px;
  bottom: 3px;
  left: 3px;
  width: calc(50% - 3px);
  border-radius: 8px;
  background: #ffffff;
  z-index: 0;
  pointer-events: none;
  transition: transform 0.36s var(--ease-out);
  will-change: transform;
}

.seg--2.seg--i1 .seg__pill {
  transform: translateX(calc(100% + 2px));
}

.seg--3 .seg__pill {
  width: calc(33.333% - 2.1px);
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
  color: var(--on-brand-muted);
  font: inherit;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.4rem 0.65rem;
  min-height: 40px;
  cursor: pointer;
  line-height: 1.2;
  flex: 1 1 0;
  min-width: 0;
  touch-action: manipulation;
  transition: color var(--duration-fast) var(--ease-out);
}

.seg__btn--icon {
  flex: 1 1 0;
  min-width: 2.35rem;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.seg__glyph {
  display: inline-block;
}

.seg__glyph--icon svg {
  display: block;
  width: 1.15em;
  height: 1.15em;
}

.seg__btn:hover:not(.seg__btn--active) {
  color: var(--on-brand);
}

.seg__btn--active {
  color: #0e2a21;
  font-weight: 700;
}

.seg__btn:focus-visible {
  outline: 3px solid var(--brand-soft);
  outline-offset: 2px;
  border-radius: 8px;
}

.seg--lang {
  flex-shrink: 0;
}

.seg__btn--lang {
  font-size: 0.8rem;
  padding: 0.38rem 0.55rem;
}

.mobile-nav .seg__btn {
  min-height: 48px;
  font-size: 0.95rem;
}

/* ── Terug naar boven ── */
.back-to-top {
  position: fixed;
  right: max(0.85rem, env(safe-area-inset-right, 0px));
  bottom: max(0.85rem, env(safe-area-inset-bottom, 0px));
  z-index: 240;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  padding: 0.5rem 0.95rem;
  min-height: 48px;
  font: inherit;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--on-brand);
  background: var(--brand);
  border: none;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  cursor: pointer;
  touch-action: manipulation;
  /* Ook opacity/transform: de wsc-fab-overgang gebruikt deze bij verschijnen/verdwijnen */
  transition:
    background var(--duration-fast) var(--ease-out),
    opacity var(--duration-normal) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

.back-to-top:hover {
  transform: translateY(-2px);
}

.back-to-top:active {
  transform: scale(0.95);
}

html.dark .back-to-top {
  background: var(--brand-soft);
  color: var(--brand);
}

.back-to-top:focus-visible {
  outline: 3px solid var(--action);
  outline-offset: 2px;
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
  .seg__pill {
    transition: none;
  }
}

/* ── Footer: diep groen ── */
.footer {
  margin-top: auto;
  background: var(--brand-deep);
  color: #9db8ac;
}

.footer__inner {
  padding-top: 3rem;
  padding-bottom: max(2rem, env(safe-area-inset-bottom, 0px));
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.footer__grid {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem 3rem;
  justify-content: space-between;
}

.footer__about {
  flex: 999 1 20rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.footer__brand {
  display: inline-flex;
  align-items: center;
  gap: 0.7rem;
  color: #eaf4ef;
  text-decoration: none;
  width: fit-content;
}

.footer__brand:hover {
  color: #eaf4ef;
  text-decoration: none;
  transform: none;
}

.footer__brand-name {
  font-weight: 800;
  font-size: 1.15rem;
  letter-spacing: -0.025em;
}

.footer__note {
  margin: 0;
  max-width: 30rem;
  font-size: 0.92rem;
  line-height: 1.55;
}

.footer__nav {
  flex: 1 1 10rem;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.1rem;
}

.footer__nav-link {
  display: inline-flex;
  align-items: center;
  min-height: 40px;
  padding: 0;
  border: 0;
  background: none;
  font: inherit;
  font-size: 0.95rem;
  font-weight: 600;
  color: #eaf4ef;
  text-decoration: none;
  cursor: pointer;
  transition:
    color var(--duration-fast) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .footer__nav-link:hover {
    transform: translateX(3px);
  }
}

.footer__cookie {
  text-align: left;
  cursor: pointer;
}

.footer__nav-link:hover {
  color: #8fe3b8;
  text-decoration: none;
  transform: none;
}

.footer__meta {
  flex: 1 1 14rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.footer__credit {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.5;
}

.footer__inline-link {
  color: #8fe3b8;
  font-weight: 600;
  text-decoration: underline;
  text-decoration-color: rgba(143, 227, 184, 0.45);
  text-underline-offset: 0.18em;
}

.footer__inline-link:hover {
  color: #c8f2dc;
  text-decoration-color: currentColor;
  transform: none;
}

.footer a:focus-visible,
.footer button:focus-visible {
  outline: 3px solid #8fe3b8;
  outline-offset: 2px;
  border-radius: 4px;
}

.footer__fineprint {
  padding-top: 1.25rem;
  border-top: 1px solid #24443a;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.footer__fineprint-line {
  margin: 0;
  font-size: 0.82rem;
  line-height: 1.5;
}

.footer__build {
  margin: 0;
  font-size: 0.7rem;
  font-variant-numeric: tabular-nums;
  word-break: break-all;
  opacity: 0.8;
}
</style>
