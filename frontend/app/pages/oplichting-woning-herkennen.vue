<template>
  <Transition name="wsc-locale" mode="out-in">
    <article :key="locale" class="main main--guide">
      <section class="wsc-hero" aria-labelledby="guide-title">
        <div class="wsc-container wsc-hero__inner">
          <Transition name="wsc-nav-back" appear>
            <nav class="guide-crumbs" :aria-label="t('guide.breadcrumbAria')">
              <NuxtLink class="wsc-back-link wsc-back-link--on-brand" :to="localePath('/')">
                <span class="wsc-back-link__arrow" aria-hidden="true">←</span>
                <span>{{ t('header.home') }}</span>
              </NuxtLink>
            </nav>
          </Transition>
          <p class="guide-eyebrow">{{ c.eyebrow }}</p>
          <h1 id="guide-title" class="wsc-hero__title">{{ c.title }}</h1>
          <p class="wsc-hero__lead">{{ c.lead }}</p>
          <p class="guide-updated">
            {{ c.updatedLabel }}:
            <time :datetime="SCAM_GUIDE_MODIFIED">{{ modifiedLabel }}</time>
          </p>
        </div>
      </section>

      <div class="wsc-container guide-body">
        <section v-reveal class="guide-answer" aria-labelledby="guide-answer-title">
          <h2 id="guide-answer-title" class="guide-answer__title">{{ c.shortAnswerTitle }}</h2>
          <ol class="guide-answer__list">
            <li v-for="(line, i) in c.shortAnswer" :key="i">{{ line }}</li>
          </ol>
          <div class="guide-answer__cta">
            <NuxtLink class="wsc-btn wsc-btn--action" :to="localePath('/check')">{{ c.ctaCheck }}</NuxtLink>
            <p class="guide-answer__note">{{ c.ctaCheckNote }}</p>
          </div>
        </section>

        <section class="guide-section" aria-labelledby="guide-signals">
          <h2 id="guide-signals" v-reveal class="guide-h2">{{ c.signalsTitle }}</h2>
          <p class="guide-intro">{{ c.signalsIntro }}</p>
          <ol class="guide-grid">
            <li v-for="(item, i) in c.signals" :key="i" v-reveal="i % 3" class="guide-card">
              <span class="guide-card__num" aria-hidden="true">{{ i + 1 }}</span>
              <h3 class="guide-card__title">{{ item.title }}</h3>
              <p class="guide-card__body">{{ item.body }}</p>
            </li>
          </ol>
        </section>

        <section class="guide-section" aria-labelledby="guide-steps">
          <h2 id="guide-steps" v-reveal class="guide-h2">{{ c.stepsTitle }}</h2>
          <p class="guide-intro">{{ c.stepsIntro }}</p>
          <ol class="guide-steps">
            <li v-for="(step, i) in c.steps" :key="i" v-reveal="i % 2" class="guide-step">
              <h3 class="guide-step__title">{{ step.title }}</h3>
              <p class="guide-step__body">{{ step.body }}</p>
              <NuxtLink v-if="i === 0" class="guide-step__link" :to="localePath('/check')">{{ c.ctaCheck }} →</NuxtLink>
              <NuxtLink v-else-if="i === 4" class="guide-step__link" :to="localePath('/huursites')">{{ c.ctaSites }} →</NuxtLink>
            </li>
          </ol>
        </section>

        <section class="guide-section" aria-labelledby="guide-types">
          <h2 id="guide-types" v-reveal class="guide-h2">{{ c.typesTitle }}</h2>
          <p class="guide-intro">{{ c.typesIntro }}</p>
          <div class="guide-grid guide-grid--types">
            <div v-for="(item, i) in c.types" :key="i" v-reveal="i % 3" class="guide-card guide-card--plain">
              <h3 class="guide-card__title">{{ item.title }}</h3>
              <p class="guide-card__body">{{ item.body }}</p>
            </div>
          </div>
        </section>

        <section v-reveal class="guide-section guide-paid" aria-labelledby="guide-paid">
          <h2 id="guide-paid" class="guide-h2">{{ c.paidTitle }}</h2>
          <p class="guide-intro">{{ c.paidIntro }}</p>
          <ol class="guide-paid__list">
            <li v-for="(line, i) in c.paidSteps" :key="i">{{ line }}</li>
          </ol>
        </section>

        <section class="guide-section" aria-labelledby="guide-faq">
          <h2 id="guide-faq" v-reveal class="guide-h2">{{ c.faqTitle }}</h2>
          <div class="guide-faq">
            <details v-for="(item, i) in c.faq" :key="i" v-reveal="i % 4" class="guide-faq__item">
              <summary class="guide-faq__q">
                <h3 class="guide-faq__q-text">{{ item.q }}</h3>
              </summary>
              <p class="guide-faq__a">{{ item.a }}</p>
            </details>
          </div>
        </section>

        <section class="guide-section" aria-labelledby="guide-sources">
          <h2 id="guide-sources" class="guide-h2 guide-h2--small">{{ c.sourcesTitle }}</h2>
          <p class="guide-intro">{{ c.sourcesIntro }}</p>
          <ul class="guide-sources">
            <li v-for="src in scamGuideSources" :key="src.url">
              <a :href="src.url" target="_blank" rel="noopener noreferrer">{{ locale === 'nl' ? src.title : src.titleEn }}</a>
            </li>
          </ul>
        </section>

        <section v-reveal class="guide-bottom">
          <h2 class="guide-bottom__title">{{ c.ctaBottomTitle }}</h2>
          <p class="guide-bottom__body">{{ c.ctaBottomBody }}</p>
          <div class="guide-bottom__actions">
            <NuxtLink class="wsc-btn wsc-btn--action" :to="localePath('/check')">{{ c.ctaCheck }}</NuxtLink>
            <NuxtLink class="guide-bottom__link" :to="localePath('/faq')">{{ c.ctaFaq }}</NuxtLink>
          </div>
        </section>
      </div>
    </article>
  </Transition>
</template>

<script setup lang="ts">
import {
  scamGuide,
  scamGuideSources,
  SCAM_GUIDE_MODIFIED,
  SCAM_GUIDE_PATH,
  SCAM_GUIDE_PUBLISHED,
} from '~/data/scamGuide'

const { t, locale } = useI18n()
const localePath = useLocalePath()
const baseUrl = useWscSiteBase()

const c = computed(() => scamGuide[locale.value === 'en' ? 'en' : 'nl'])

const modifiedLabel = computed(() =>
  new Date(`${SCAM_GUIDE_MODIFIED}T12:00:00Z`).toLocaleDateString(
    locale.value === 'en' ? 'en-GB' : 'nl-NL',
    { day: 'numeric', month: 'long', year: 'numeric' },
  ),
)

/** Article + FAQPage + BreadcrumbList: helpt Google de pagina als gids over woningoplichting te begrijpen. */
const guideJsonLd = computed(() => {
  const base = baseUrl.value
  if (!base)
    return null

  const url = `${base}${SCAM_GUIDE_PATH}`
  const content = c.value

  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'Article',
        '@id': `${url}#article`,
        headline: content.title,
        description: content.metaDescription,
        inLanguage: locale.value === 'en' ? 'en' : 'nl-NL',
        url,
        mainEntityOfPage: url,
        image: `${base}/og.png`,
        datePublished: SCAM_GUIDE_PUBLISHED,
        dateModified: SCAM_GUIDE_MODIFIED,
        author: { '@id': `${base}/#organization` },
        publisher: { '@id': `${base}/#organization` },
        isPartOf: { '@id': `${base}/#website` },
        about: [
          { '@type': 'Thing', name: 'Woningoplichting' },
          { '@type': 'Thing', name: 'Huurfraude' },
          { '@type': 'Thing', name: 'Nep huuradvertenties herkennen' },
        ],
      },
      {
        '@type': 'FAQPage',
        '@id': `${url}#faq`,
        mainEntity: content.faq.map(item => ({
          '@type': 'Question',
          name: item.q,
          acceptedAnswer: { '@type': 'Answer', text: item.a },
        })),
      },
      {
        '@type': 'BreadcrumbList',
        itemListElement: [
          { '@type': 'ListItem', position: 1, name: 'De Huur Radar', item: base },
          { '@type': 'ListItem', position: 2, name: content.breadcrumb, item: url },
        ],
      },
    ],
  }
})

useWscSeo({
  title: () => c.value.metaTitle,
  description: () => c.value.metaDescription,
  path: SCAM_GUIDE_PATH,
})

useHead(() => ({
  meta: [{ name: 'keywords', content: t('guide.keywords') }],
  script: guideJsonLd.value
    ? [{ type: 'application/ld+json', innerHTML: JSON.stringify(guideJsonLd.value) }]
    : [],
}))
</script>

<style scoped>
.main--guide {
  padding-bottom: 4rem;
}

.guide-crumbs {
  margin: 0 0 0.5rem;
}

.guide-eyebrow {
  margin: 0 0 0.5rem;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--on-brand-muted);
}

.guide-updated {
  margin: 1rem 0 0;
  font-size: 0.9rem;
  color: var(--on-brand-muted);
}

.guide-body {
  margin-top: clamp(1.5rem, 4vw, 2.5rem);
  display: grid;
  gap: clamp(2.5rem, 6vw, 4rem);
}

.guide-answer {
  padding: clamp(1.25rem, 3vw, 2rem);
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
  box-shadow: inset 0 0 0 1.5px var(--border-subtle);
}

.guide-answer__title {
  margin: 0 0 1rem;
  font-size: clamp(1.25rem, 2.6vw, 1.6rem);
  font-weight: 800;
  letter-spacing: -0.02em;
}

.guide-answer__list {
  margin: 0;
  padding-left: 1.3rem;
  display: grid;
  gap: 0.55rem;
  line-height: 1.55;
  max-width: 75ch;
  color: var(--text-secondary);
}

.guide-answer__cta {
  margin-top: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem 1.25rem;
}

.guide-answer__note {
  margin: 0;
  flex: 1 1 16rem;
  font-size: 0.92rem;
  color: var(--text-secondary);
}

.guide-h2 {
  margin: 0 0 0.6rem;
  font-size: clamp(1.45rem, 3.2vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.025em;
  line-height: 1.2;
}

.guide-h2--small {
  font-size: clamp(1.2rem, 2.4vw, 1.45rem);
}

.guide-intro {
  margin: 0 0 1.25rem;
  max-width: 70ch;
  line-height: 1.6;
  color: var(--text-secondary);
}

.guide-grid {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 19rem), 1fr));
  gap: 0.9rem;
}

.guide-card {
  position: relative;
  padding: 1.25rem 1.25rem 1.35rem;
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
}

.guide-card__num {
  display: inline-grid;
  place-items: center;
  width: 2rem;
  height: 2rem;
  margin-bottom: 0.6rem;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand);
  font-weight: 800;
}

.guide-card--plain {
  box-shadow: inset 0 0 0 1.5px var(--border-subtle);
  background: transparent;
}

.guide-card__title,
.guide-step__title {
  margin: 0 0 0.4rem;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.35;
}

.guide-card__body,
.guide-step__body {
  margin: 0;
  line-height: 1.55;
  color: var(--text-secondary);
}

.guide-steps {
  margin: 0;
  padding: 0;
  list-style: none;
  counter-reset: guide-step;
  display: grid;
  gap: 0.9rem;
  max-width: 52rem;
}

.guide-step {
  counter-increment: guide-step;
  position: relative;
  padding: 1.2rem 1.25rem 1.25rem 4rem;
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
}

.guide-step::before {
  content: counter(guide-step);
  position: absolute;
  left: 1.1rem;
  top: 1.1rem;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: var(--action);
  color: var(--on-action);
  font-weight: 800;
}

.guide-step__link {
  display: inline-block;
  margin-top: 0.6rem;
  font-weight: 700;
}

.guide-paid {
  padding: clamp(1.25rem, 3vw, 2rem);
  border-radius: var(--radius-lg);
  background: var(--brand);
  color: var(--on-brand);
}

.guide-paid .guide-h2 {
  color: var(--on-brand);
}

.guide-paid .guide-intro,
.guide-paid__list {
  color: var(--on-brand-muted);
}

.guide-paid__list {
  margin: 0;
  padding-left: 1.3rem;
  display: grid;
  gap: 0.5rem;
  line-height: 1.55;
  max-width: 70ch;
}

.guide-faq {
  display: grid;
  gap: 0.6rem;
  max-width: 52rem;
}

.guide-faq__item {
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
}

.guide-faq__q {
  list-style: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.1rem 1.25rem;
  border-radius: var(--radius-lg);
}

.guide-faq__q::-webkit-details-marker {
  display: none;
}

.guide-faq__q::after {
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

.guide-faq__item[open] .guide-faq__q::after {
  content: '−';
  background: var(--action);
  color: var(--on-action);
}

.guide-faq__q:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

.guide-faq__q-text {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.35;
  color: var(--text-primary);
}

.guide-faq__a {
  margin: 0;
  padding: 0 1.25rem 1.25rem;
  max-width: 70ch;
  line-height: 1.6;
  color: var(--text-secondary);
}

@media (prefers-reduced-motion: no-preference) {
  .guide-faq__item[open] .guide-faq__a {
    animation: guide-open 0.35s var(--ease-out);
  }

  .guide-faq__q:hover::after {
    transform: rotate(90deg);
  }
}

@keyframes guide-open {
  from {
    opacity: 0;
    translate: 0 -6px;
  }
}

.guide-sources {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
  gap: 0.4rem 1rem;
}

.guide-sources a {
  display: inline-block;
  padding: 0.3rem 0;
  font-weight: 600;
  line-height: 1.4;
}

.guide-bottom {
  padding: clamp(1.5rem, 4vw, 2.5rem);
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
  text-align: center;
}

.guide-bottom__title {
  margin: 0 0 0.5rem;
  font-size: clamp(1.35rem, 3vw, 1.8rem);
  font-weight: 800;
  letter-spacing: -0.025em;
}

.guide-bottom__body {
  margin: 0 auto 1.25rem;
  max-width: 55ch;
  line-height: 1.55;
  color: var(--text-secondary);
}

.guide-bottom__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 0.75rem 1.5rem;
}

.guide-bottom__link {
  font-weight: 700;
}
</style>
