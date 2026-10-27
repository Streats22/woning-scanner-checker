<template>
  <Transition name="wsc-locale" mode="out-in">
    <div :key="locale" class="main main--faq">
      <section class="wsc-hero" aria-labelledby="faq-title">
        <div class="wsc-container wsc-hero__inner">
          <Transition name="wsc-nav-back" appear>
            <p class="faq-nav">
              <NuxtLink class="faq-nav__link wsc-back-link wsc-back-link--on-brand" :to="localePath('/')">
                <span class="wsc-back-link__arrow" aria-hidden="true">←</span>
                <span class="faq-nav__text">{{ t('faq.backToHome') }}</span>
              </NuxtLink>
            </p>
          </Transition>
          <h1 id="faq-title" class="wsc-hero__title">{{ t('faq.title') }}</h1>
          <p class="wsc-hero__lead">{{ t('faq.intro') }}</p>
        </div>
      </section>

      <div class="wsc-container faq-body">
        <div class="faq-layout">
          <div class="faq-main">
            <div class="faq-list" role="list">
              <details
                v-for="(n, i) in faqQuestionIds"
                :key="n"
                v-reveal="i % 4"
                class="faq-item"
                role="listitem"
              >
                <summary class="faq-item__summary">{{ t('faq.q' + n) }}</summary>
                <p class="faq-item__body">{{ t('faq.a' + n) }}</p>
              </details>
            </div>

            <details id="faq-sources" class="faq-sources-details">
              <summary class="faq-sources-details__summary">
                <span class="faq-sources-details__title">{{ t('faq.sourcesTitle') }}</span>
                <span class="faq-sources-details__count" aria-hidden="true">{{ faqSources.length }}</span>
              </summary>
              <div class="faq-sources-details__panel">
                <p class="faq-sources__intro">{{ t('faq.sourcesIntro') }}</p>
                <ul class="faq-sources">
                  <li v-for="(src, i) in faqSources" :key="i">
                    <a
                      class="faq-sources__link"
                      :href="src.url"
                      target="_blank"
                      rel="noopener noreferrer"
                    >{{ locale === 'nl' ? src.title : src.titleEn }}</a>
                  </li>
                </ul>
              </div>
            </details>
          </div>

          <aside v-reveal="1" class="contact-card" aria-labelledby="contact-title">
            <h2 id="contact-title" class="contact-card__title">{{ t('faq.contactTitle') }}</h2>
            <p class="contact-card__intro">{{ t('faq.contactIntro') }}</p>
            <a class="wsc-btn wsc-btn--action contact-card__mail" href="mailto:info@streatsdesign.nl">info@streatsdesign.nl</a>
            <p class="contact-card__note">{{ t('faq.contactNote') }}</p>
          </aside>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { faqSources } from '~/data/faqSources'

const { t, locale } = useI18n()
const localePath = useLocalePath()
const baseUrl = useWscSiteBase()

const faqQuestionIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as const

const faqJsonLd = computed(() => {
  const base = baseUrl.value
  if (!base)
    return null

  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqQuestionIds.map((n) => ({
      '@type': 'Question',
      name: t(`faq.q${n}`),
      acceptedAnswer: {
        '@type': 'Answer',
        text: t(`faq.a${n}`),
      },
    })),
  }
})

useWscSeo({
  title: () => t('faq.metaTitle'),
  description: () => t('faq.metaDescription'),
  path: '/faq',
})

useHead(() => ({
  script: faqJsonLd.value
    ? [
        {
          type: 'application/ld+json',
          innerHTML: JSON.stringify(faqJsonLd.value),
        },
      ]
    : [],
}))
</script>

<style scoped>
.main--faq {
  padding-bottom: 4rem;
}

.faq-nav {
  margin: 0 0 0.5rem;
}

.faq-body {
  margin-top: clamp(1.5rem, 4vw, 2.5rem);
}

.faq-layout {
  display: grid;
  gap: 1.25rem;
  align-items: start;
}

@media (min-width: 960px) {
  .faq-layout {
    grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr);
  }
  .contact-card {
    position: sticky;
    top: 5.5rem;
  }
}

.faq-list {
  display: grid;
  gap: 0.6rem;
}

.faq-item {
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
}

.faq-item__summary,
.faq-sources-details__summary {
  list-style: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.1rem 1.25rem;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.35;
  color: var(--text-primary);
  border-radius: var(--radius-lg);
}

.faq-item__summary::-webkit-details-marker,
.faq-sources-details__summary::-webkit-details-marker {
  display: none;
}

.faq-item__summary::after,
.faq-sources-details__summary::after {
  content: '+';
  flex: 0 0 auto;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand);
  font-size: 1.2rem;
  font-weight: 700;
  display: grid;
  place-items: center;
  transition: transform var(--duration-fast) var(--ease-out);
}

.faq-item[open] .faq-item__summary::after,
.faq-sources-details[open] .faq-sources-details__summary::after {
  content: '−';
  background: var(--action);
  color: var(--on-action);
}

.faq-item__summary:focus-visible,
.faq-sources-details__summary:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

@media (prefers-reduced-motion: no-preference) {
  .faq-item[open] .faq-item__body,
  .faq-sources-details[open] .faq-sources-details__panel {
    animation: faq-open 0.35s var(--ease-out);
  }

  .faq-item__summary:hover::after,
  .faq-sources-details__summary:hover::after {
    transform: rotate(90deg);
  }
}

@keyframes faq-open {
  from {
    opacity: 0;
    translate: 0 -6px;
  }
}

.faq-item__body {
  margin: 0;
  padding: 0 1.25rem 1.25rem;
  max-width: 70ch;
  line-height: 1.6;
  color: var(--text-secondary);
}

.faq-sources-details {
  margin-top: 1.5rem;
  border-radius: var(--radius-lg);
  border: 1.5px solid var(--border-subtle);
}

.faq-sources-details__title {
  flex: 1;
}

.faq-sources-details__count {
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  background: var(--surface-muted);
  color: var(--text-secondary);
  font-size: 0.85rem;
  font-weight: 700;
}

.faq-sources-details__panel {
  padding: 0 1.25rem 1.25rem;
}

.faq-sources__intro {
  margin: 0 0 1rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.faq-sources {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
  gap: 0.4rem 1rem;
}

.faq-sources__link {
  display: inline-block;
  padding: 0.3rem 0;
  font-weight: 600;
  line-height: 1.4;
}

.contact-card {
  padding: clamp(1.25rem, 3vw, 1.75rem);
  border-radius: var(--radius-lg);
  background: var(--brand);
  color: var(--on-brand);
}

.contact-card__title {
  margin: 0 0 0.5rem;
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: var(--on-brand);
}

.contact-card__intro,
.contact-card__note {
  margin: 0 0 1rem;
  line-height: 1.55;
  color: var(--on-brand-muted);
}

.contact-card__note {
  margin: 1rem 0 0;
  font-size: 0.9rem;
}

.contact-card__mail {
  width: 100%;
  overflow-wrap: anywhere;
}
</style>
