<template>
  <Transition name="wsc-locale" mode="out-in">
    <div :key="locale" class="main main--sites">
      <Transition name="wsc-nav-back" appear>
        <p class="sites-nav">
          <NuxtLink class="sites-nav__link wsc-back-link" :to="localePath('/')">
            <span class="wsc-back-link__arrow" aria-hidden="true">←</span>
            <span class="sites-nav__text">{{ t('sites.backToHome') }}</span>
          </NuxtLink>
        </p>
      </Transition>

      <section class="panel panel--sites panel--rise" aria-labelledby="sites-title">
        <h1 id="sites-title" class="section-title font-display section-title--first">{{ t('sites.title') }}</h1>
        <p class="body-text sites-intro">{{ t('sites.intro') }}</p>
        <p class="body-text sites-disclaimer muted">{{ t('sites.disclaimer') }}</p>

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

        <div v-for="region in groupedRegions" :key="region.id" class="sites-cat">
          <h2 class="sites-cat__title font-display">{{ t('sites.region.' + region.id) }}</h2>
          <ul class="sites-list">
            <li v-for="site in region.items" :key="site.url" class="sites-item">
              <div class="sites-item__head">
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
              <p class="sites-item__note muted">
                {{ locale === 'nl' ? site.noteNl : site.noteEn }}
              </p>
            </li>
          </ul>
        </div>

        <p class="body-text sites-cta">
          <NuxtLink class="sites-cta__btn" :to="localePath('/check')">{{ t('sites.ctaCheck') }}</NuxtLink>
        </p>
        <p class="body-text muted sites-faqlink">
          <NuxtLink class="sites-faqlink__inner" :to="localePath('/faq')">{{ t('sites.ctaFaq') }}</NuxtLink>
        </p>
      </section>
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
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.main {
  flex: 1;
  max-width: 960px;
  margin: 0 auto;
  width: 100%;
  min-width: 0;
  padding:
    max(1rem, env(safe-area-inset-top, 0px))
    max(1.25rem, env(safe-area-inset-right, 0px))
    max(3rem, env(safe-area-inset-bottom, 0px))
    max(1.25rem, env(safe-area-inset-left, 0px));
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.main--sites {
  padding-top: 1rem;
  gap: 1.35rem;
}

.sites-nav {
  margin: 0 0 0.15rem;
}

.panel {
  background: var(--surface-elevated);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-subtle);
  box-shadow: var(--shadow-md);
  padding: 1.5rem 1.4rem;
  transition:
    box-shadow var(--duration-normal) var(--ease-out),
    border-color var(--duration-normal) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

@media (prefers-reduced-motion: no-preference) {
  .panel--rise {
    animation: wsc-panel-rise 0.58s var(--ease-out) backwards;
  }
}

.panel--sites {
  border-color: color-mix(in srgb, var(--accent) 12%, var(--border-subtle));
}

.section-title {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 0 0 0.75rem;
  color: var(--text-primary);
}

.sites-intro {
  margin: 0 0 0.75rem;
  color: var(--text-secondary);
  line-height: 1.65;
}

.sites-disclaimer {
  margin: 0 0 1rem;
  font-size: 0.85rem;
}

.sites-legend {
  list-style: none;
  margin: 0 0 1.25rem;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 0.85rem;
}

.sites-legend__item {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.sites-cat {
  margin: 0 0 1.5rem;
}

.sites-cat:last-of-type {
  margin-bottom: 0.5rem;
}

.sites-cat__title {
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 0.55rem;
  padding-bottom: 0.35rem;
  border-bottom: 1px solid var(--border-subtle);
}

.sites-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.55rem;
}

@media (min-width: 700px) {
  .sites-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.sites-item {
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  background: var(--surface-muted);
  padding: 0.7rem 0.85rem 0.8rem;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .sites-item:hover {
    border-color: color-mix(in srgb, var(--accent) 22%, var(--border-subtle));
    box-shadow: var(--shadow-sm);
  }
}

.sites-item__head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.45rem 0.65rem;
  margin: 0 0 0.25rem;
}

.sites-item__link {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: underline;
  text-decoration-color: color-mix(in srgb, var(--accent) 38%, transparent);
  text-decoration-thickness: 1.5px;
  text-underline-offset: 0.18em;
  transition:
    color var(--duration-fast) var(--ease-out),
    text-decoration-color var(--duration-fast) var(--ease-out),
    text-decoration-thickness var(--duration-fast) var(--ease-out);
}

.sites-item__link:hover {
  color: var(--accent-hover);
  text-decoration-color: color-mix(in srgb, var(--accent-hover) 72%, transparent);
  text-decoration-thickness: 2px;
}

.sites-item__cities {
  margin: 0 0 0.3rem;
  font-size: 0.78rem;
  line-height: 1.45;
  color: var(--text-tertiary);
}

.sites-item__cities-label {
  font-weight: 600;
  color: var(--text-secondary);
  margin-right: 0.25rem;
}

.sites-item__note {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.5;
}

.sites-cost {
  display: inline-flex;
  align-items: center;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  padding: 0.18rem 0.55rem;
  border-radius: 999px;
  border: 1px solid transparent;
  white-space: nowrap;
}

.sites-cost--free {
  color: #0f5132;
  background: #d1f4dd;
  border-color: #a7e3bd;
}

.sites-cost--freemium {
  color: #663b00;
  background: #fff1cc;
  border-color: #f6dd97;
}

.sites-cost--paid {
  color: #842029;
  background: #fcdde0;
  border-color: #f1b6bb;
}

html.dark .sites-cost--free {
  color: #b9f0ce;
  background: color-mix(in srgb, #0f5132 30%, transparent);
  border-color: color-mix(in srgb, #0f5132 55%, transparent);
}

html.dark .sites-cost--freemium {
  color: #ffd681;
  background: color-mix(in srgb, #663b00 35%, transparent);
  border-color: color-mix(in srgb, #663b00 60%, transparent);
}

html.dark .sites-cost--paid {
  color: #ffb1b8;
  background: color-mix(in srgb, #842029 30%, transparent);
  border-color: color-mix(in srgb, #842029 55%, transparent);
}

.sites-cta {
  margin: 1.25rem 0 0.25rem;
  text-align: center;
}

.sites-cta__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.7rem 1.35rem;
  min-height: 48px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #fff;
  background: var(--accent);
  border-radius: var(--radius-md);
  text-decoration: none;
  box-shadow: var(--shadow-sm);
  touch-action: manipulation;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

.sites-cta__btn:hover {
  background: var(--accent-hover);
  box-shadow: var(--shadow-md);
}

.sites-cta__btn:focus-visible {
  outline: 2px solid var(--surface-elevated);
  outline-offset: 2px;
  box-shadow: 0 0 0 4px var(--accent-muted);
}

.sites-faqlink {
  margin: 0.5rem 0 0;
  text-align: center;
  font-size: 0.85rem;
}

.sites-faqlink__inner {
  color: var(--accent);
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.body-text {
  font-size: 0.9375rem;
  line-height: 1.65;
}

.muted {
  color: var(--text-secondary);
}
</style>
