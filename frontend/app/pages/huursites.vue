<template>
  <Transition name="wsc-locale" mode="out-in">
    <div :key="locale" class="main main--sites">
      <section class="wsc-hero" aria-labelledby="sites-title">
        <div class="wsc-container wsc-hero__inner">
          <Transition name="wsc-nav-back" appear>
            <p class="sites-nav">
              <NuxtLink class="sites-nav__link wsc-back-link wsc-back-link--on-brand" :to="localePath('/')">
                <span class="wsc-back-link__arrow" aria-hidden="true">←</span>
                <span class="sites-nav__text">{{ t('sites.backToHome') }}</span>
              </NuxtLink>
            </p>
          </Transition>
          <h1 id="sites-title" class="wsc-hero__title">{{ t('sites.title') }}</h1>
          <p class="wsc-hero__lead">{{ t('sites.intro') }}</p>
        </div>
      </section>

      <div class="wsc-container sites-body">
        <div class="sites-toolbar">
          <div class="sites-filter" role="group" :aria-label="t('sites.filterAria')">
            <button
              type="button"
              class="sites-chip"
              :class="{ 'sites-chip--on': activeRegion === 'all' }"
              :aria-pressed="activeRegion === 'all'"
              @click="activeRegion = 'all'"
            >{{ t('sites.filterAll') }}</button>
            <button
              v-for="region in groupedRegions"
              :key="region.id"
              type="button"
              class="sites-chip"
              :class="{ 'sites-chip--on': activeRegion === region.id }"
              :aria-pressed="activeRegion === region.id"
              @click="activeRegion = region.id"
            >{{ t('sites.region.' + region.id) }}</button>
          </div>
          <ul class="sites-legend" :aria-label="t('sites.legendAria')">
            <li class="sites-legend__item">
              <span class="sites-cost sites-cost--free">{{ t('sites.costFree') }}</span>
              <span class="sites-legend__text">{{ t('sites.legendFree') }}</span>
            </li>
            <li class="sites-legend__item">
              <span class="sites-cost sites-cost--freemium">{{ t('sites.costFreemium') }}</span>
              <span class="sites-legend__text">{{ t('sites.legendFreemium') }}</span>
            </li>
            <li class="sites-legend__item">
              <span class="sites-cost sites-cost--paid">{{ t('sites.costPaid') }}</span>
              <span class="sites-legend__text">{{ t('sites.legendPaid') }}</span>
            </li>
          </ul>
        </div>

        <p class="sites-count" aria-live="polite">{{ t('sites.resultCount', { n: visibleCount }) }}</p>

        <div v-for="region in visibleRegions" :key="region.id" class="sites-cat">
          <h2 class="sites-cat__title">{{ t('sites.region.' + region.id) }}</h2>
          <ul class="sites-list">
            <li v-for="site in region.items" :key="site.url" class="sites-item">
              <div class="sites-item__head">
                <span class="sites-item__mono" aria-hidden="true">{{ site.name.charAt(0) }}</span>
                <a
                  class="sites-item__link"
                  :href="site.url"
                  target="_blank"
                  rel="noopener noreferrer"
                >{{ site.name }}</a>
                <span
                  class="sites-cost"
                  :class="`sites-cost--${site.cost}`"
                  :aria-label="t('sites.costAria.' + site.cost)"
                >{{ t('sites.cost' + capitalize(site.cost)) }}</span>
              </div>
              <p v-if="site.cities && site.cities.length" class="sites-item__cities">
                <span class="sites-item__cities-label">{{ t('sites.citiesLabel') }}:</span>
                {{ site.cities.join(' · ') }}
              </p>
              <p class="sites-item__note">
                {{ locale === 'nl' ? site.noteNl : site.noteEn }}
              </p>
            </li>
          </ul>
        </div>

        <p class="sites-disclaimer">{{ t('sites.disclaimer') }}</p>

        <div class="sites-cta">
          <NuxtLink class="wsc-btn wsc-btn--action" :to="localePath('/check')">{{ t('sites.ctaCheck') }}</NuxtLink>
          <NuxtLink class="sites-cta__faq" :to="localePath('/faq')">{{ t('sites.ctaFaq') }}</NuxtLink>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { rentalSites, type RentalSiteRegion } from '~/data/rentalSites'

const { t, locale } = useI18n()
const localePath = useLocalePath()
const baseUrl = useWscSiteBase()

const regionOrder: RentalSiteRegion[] = [
  'landelijk',
  'multi',
  'noord-holland',
  'zuid-holland',
  'utrecht',
  'noord-brabant',
  'limburg',
  'internationaal',
]

const groupedRegions = computed(() =>
  regionOrder
    .map(id => ({ id, items: rentalSites.filter(s => s.region === id) }))
    .filter(group => group.items.length > 0),
)

const activeRegion = ref<RentalSiteRegion | 'all'>('all')

const visibleRegions = computed(() =>
  activeRegion.value === 'all'
    ? groupedRegions.value
    : groupedRegions.value.filter(g => g.id === activeRegion.value),
)

const visibleCount = computed(() =>
  visibleRegions.value.reduce((sum, g) => sum + g.items.length, 0),
)

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const itemListJsonLd = computed(() => {
  const base = baseUrl.value
  if (!base)
    return null

  return {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    name: t('sites.title'),
    description: t('sites.metaDescription'),
    numberOfItems: rentalSites.length,
    itemListElement: rentalSites.map((site, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      item: {
        '@type': 'WebSite',
        name: site.name,
        url: site.url,
        description: locale.value === 'nl' ? site.noteNl : site.noteEn,
      },
    })),
  }
})

useWscSeo({
  title: () => t('sites.metaTitle'),
  description: () => t('sites.metaDescription'),
  path: '/huursites',
})

useHead(() => ({
  script: itemListJsonLd.value
    ? [
        {
          type: 'application/ld+json',
          innerHTML: JSON.stringify(itemListJsonLd.value),
        },
      ]
    : [],
}))
</script>

<style scoped>
.main--sites {
  padding-bottom: 4rem;
}

.sites-nav {
  margin: 0 0 0.5rem;
}

.sites-body {
  margin-top: clamp(1.5rem, 4vw, 2.5rem);
}

.sites-toolbar {
  display: grid;
  gap: 1rem;
}

.sites-filter {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.sites-chip {
  min-height: 44px;
  padding: 0 1rem;
  border: 1.5px solid var(--border-subtle);
  border-radius: 999px;
  background: var(--surface-page);
  color: var(--text-primary);
  font: inherit;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition:
    background var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out);
}

.sites-chip:hover {
  border-color: var(--border-strong);
}

.sites-chip--on,
.sites-chip--on:hover {
  background: var(--brand);
  border-color: var(--brand);
  color: var(--on-brand);
}

html.dark .sites-chip--on,
html.dark .sites-chip--on:hover {
  background: var(--brand-soft);
  border-color: var(--brand-soft);
  color: var(--brand);
}

.sites-chip:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

.sites-legend {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1.25rem;
}

.sites-legend__item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.sites-count {
  margin: 1.25rem 0 0;
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--text-tertiary);
}

.sites-cat {
  margin-top: 1.75rem;
}

.sites-cat__title {
  margin: 0 0 0.85rem;
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: var(--text-primary);
}

.sites-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
  gap: 0.85rem;
}

.sites-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1.15rem 1.2rem;
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
}

.sites-item__head {
  display: flex;
  align-items: center;
  gap: 0.7rem;
}

.sites-item__mono {
  flex: 0 0 auto;
  width: 2.4rem;
  height: 2.4rem;
  border-radius: 12px;
  background: var(--brand);
  color: var(--brand-soft);
  font-weight: 800;
  font-size: 1.05rem;
  display: grid;
  place-items: center;
  text-transform: uppercase;
}

.sites-item__link {
  flex: 1;
  min-width: 0;
  font-weight: 800;
  font-size: 1.05rem;
  color: var(--text-primary);
  text-decoration: none;
  overflow-wrap: anywhere;
}

.sites-item__link::after {
  content: ' ↗';
  color: var(--accent);
  font-weight: 700;
}

.sites-item__link:hover {
  color: var(--accent);
  text-decoration: underline;
}

.sites-item__cities {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.45;
  color: var(--text-secondary);
}

.sites-item__cities-label {
  font-weight: 700;
}

.sites-item__note {
  margin: 0;
  font-size: 0.93rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.sites-cost {
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  min-height: 26px;
  padding: 0 0.6rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
  white-space: nowrap;
}

.sites-cost--free {
  background: var(--risk-low-bg);
  color: var(--risk-low);
}

.sites-cost--freemium {
  background: var(--risk-mid-bg);
  color: var(--risk-mid);
}

.sites-cost--paid {
  background: var(--risk-high-bg);
  color: var(--risk-high);
}

.sites-disclaimer {
  margin: 2rem 0 0;
  max-width: 70ch;
  font-size: 0.9rem;
  line-height: 1.55;
  color: var(--text-tertiary);
}

.sites-cta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem 1.5rem;
  margin-top: 2rem;
  padding: clamp(1.25rem, 3vw, 2rem);
  border-radius: var(--radius-lg);
  background: var(--brand);
}

.sites-cta__faq {
  color: var(--brand-soft);
  font-weight: 700;
}

.sites-cta__faq:hover {
  color: var(--on-brand);
}
</style>
