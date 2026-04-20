<template>
  <Transition name="wsc-locale" mode="out-in">
    <div :key="locale" class="main main--faq">
    <p class="faq-nav">
      <NuxtLink class="faq-nav__link" :to="localePath('/')">
        <span class="faq-nav__arrow" aria-hidden="true">←</span>
        <span class="faq-nav__text">{{ t('faq.backToHome') }}</span>
      </NuxtLink>
    </p>

    <section class="panel panel--faq panel--rise" aria-labelledby="faq-title">
      <h1 id="faq-title" class="section-title font-display section-title--first">{{ t('faq.title') }}</h1>
      <p class="body-text faq-intro">{{ t('faq.intro') }}</p>
      <div class="faq-list" role="list">
        <details
          v-for="n in faqQuestionIds"
          :key="n"
          class="faq-item"
          role="listitem"
        >
          <summary class="faq-item__summary font-display">{{ t('faq.q' + n) }}</summary>
          <p class="body-text faq-item__body">{{ t('faq.a' + n) }}</p>
        </details>
      </div>
      <h2 class="faq-sources__title font-display">{{ t('faq.sourcesTitle') }}</h2>
      <p class="body-text muted faq-sources__intro">{{ t('faq.sourcesIntro') }}</p>
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
    </section>

    <section class="panel panel--contact panel--rise panel--rise-delay" aria-labelledby="contact-title">
      <h2 id="contact-title" class="section-title font-display">{{ t('faq.contactTitle') }}</h2>
      <p class="body-text contact-intro">{{ t('faq.contactIntro') }}</p>
      <p class="contact-line">
        <a class="contact-mail" href="mailto:streatsDesign@outlook.com">streatsDesign@outlook.com</a>
      </p>
      <p class="body-text muted contact-note">{{ t('faq.contactNote') }}</p>
    </section>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { faqSources } from '~/data/faqSources'

const { t, locale } = useI18n()
const localePath = useLocalePath()
const baseUrl = useWscSiteBase()

const faqQuestionIds = [1, 2, 3, 4, 5, 6, 7, 8, 9] as const

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
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.main {
  flex: 1;
  max-width: 960px;
  margin: 0 auto;
  width: 100%;
  padding: 1.75rem 1.25rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.main--faq {
  padding-top: 1rem;
  gap: 1.35rem;
}

.faq-nav {
  margin: 0 0 0.15rem;
}

@media (prefers-reduced-motion: no-preference) {
  .faq-nav {
    animation: wsc-fade-in-up var(--duration-slow) var(--ease-out) backwards;
  }
}

.faq-nav__link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: none;
  padding: 0.35rem 0.2rem;
  border-radius: var(--radius-sm);
  transition:
    color var(--duration-fast) var(--ease-out),
    gap var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out);
}

.faq-nav__arrow {
  display: inline-block;
  transition: transform var(--duration-normal) var(--ease-spring);
}

.faq-nav__link:hover {
  text-decoration: none;
  color: var(--accent-hover);
  background: var(--accent-muted);
}

.faq-nav__link:hover .faq-nav__arrow {
  transform: translateX(-3px);
}

@media (prefers-reduced-motion: reduce) {
  .faq-nav__link:hover .faq-nav__arrow {
    transform: none;
  }
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

  .panel--rise-delay {
    animation-delay: 0.12s;
  }

  .panel--faq:hover,
  .panel--contact:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-panel-hover);
    border-color: color-mix(in srgb, var(--accent) 12%, var(--border-subtle));
  }
}

.panel--faq {
  border-color: color-mix(in srgb, var(--accent) 12%, var(--border-subtle));
}

.panel--contact {
  border-color: color-mix(in srgb, var(--accent) 18%, var(--border-subtle));
}

.section-title {
  font-size: 1.05rem;
  font-weight: 600;
  margin: 0 0 0.75rem;
  color: var(--text-primary);
}

.section-title--first {
  font-size: 1.15rem;
}

.faq-intro {
  margin: 0 0 1rem;
  color: var(--text-secondary);
  line-height: 1.65;
}

.faq-list {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.faq-item {
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  background: var(--surface-muted);
  padding: 0;
  overflow: hidden;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

@media (prefers-reduced-motion: no-preference) {
  .faq-item:nth-child(1) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.06s backwards;
  }

  .faq-item:nth-child(2) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.08s backwards;
  }

  .faq-item:nth-child(3) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.1s backwards;
  }

  .faq-item:nth-child(4) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.12s backwards;
  }

  .faq-item:nth-child(5) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.14s backwards;
  }

  .faq-item:nth-child(6) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.16s backwards;
  }

  .faq-item:nth-child(7) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.18s backwards;
  }

  .faq-item:nth-child(8) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.2s backwards;
  }

  .faq-item:nth-child(9) {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.22s backwards;
  }

  .faq-item:hover {
    border-color: color-mix(in srgb, var(--accent) 18%, var(--border-subtle));
    box-shadow: var(--shadow-sm);
  }

  .faq-item[open] {
    border-color: color-mix(in srgb, var(--accent) 28%, var(--border-subtle));
    box-shadow: var(--shadow-sm);
  }
}

.faq-item__summary {
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 600;
  padding: 0.75rem 2.35rem 0.75rem 1rem;
  list-style: none;
  color: var(--text-primary);
  position: relative;
  transition:
    color var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out),
    transform 0.12s var(--ease-out);
}

.faq-item__summary::after {
  content: '▸';
  position: absolute;
  right: 0.85rem;
  top: 50%;
  font-size: 0.7rem;
  line-height: 1;
  opacity: 0.5;
  transform: translateY(-50%) rotate(0deg);
  transition: transform 0.38s var(--ease-spring), opacity var(--duration-fast) var(--ease-out);
}

.faq-item[open] .faq-item__summary::after {
  transform: translateY(-50%) rotate(90deg);
  opacity: 0.75;
}

@media (prefers-reduced-motion: reduce) {
  .faq-item__summary::after {
    transition: none;
  }
}

.faq-item__summary:hover {
  color: var(--accent);
  background: color-mix(in srgb, var(--surface-page) 55%, var(--surface-muted));
}

@media (prefers-reduced-motion: no-preference) {
  .faq-item__summary:active {
    transform: scale(0.985);
  }
}

.faq-item__summary::-webkit-details-marker {
  display: none;
}

.faq-item__body {
  margin: 0;
  padding: 0 1rem 0.9rem;
  border-top: 1px solid var(--border-subtle);
  padding-top: 0.65rem;
}

@media (prefers-reduced-motion: no-preference) {
  .faq-item[open] .faq-item__body {
    animation: wsc-fade-in 0.32s var(--ease-out) both;
  }
}

.faq-sources__title {
  font-size: 1.05rem;
  margin: 1.25rem 0 0.5rem;
}

.faq-sources__intro {
  margin: 0 0 0.75rem;
  font-size: 0.88rem;
}

.faq-sources {
  margin: 0;
  padding-left: 1.2rem;
  color: var(--text-secondary);
  font-size: 0.88rem;
  line-height: 1.55;
}

.faq-sources li {
  margin: 0.35rem 0;
}

.faq-sources__link {
  color: var(--accent);
  text-decoration: underline;
  text-underline-offset: 0.15em;
  transition: color var(--duration-fast) var(--ease-out);
}

.faq-sources__link:hover {
  color: var(--accent-hover);
}

@media (prefers-reduced-motion: no-preference) {
  .faq-sources {
    animation: wsc-fade-in 0.5s var(--ease-out) 0.28s backwards;
  }
}

.body-text {
  font-size: 0.9375rem;
  line-height: 1.65;
}

.muted {
  color: var(--text-secondary);
  font-size: 0.9375rem;
}

.contact-intro {
  margin: 0 0 0.75rem;
  color: var(--text-secondary);
}

.contact-line {
  margin: 0 0 0.5rem;
}

.contact-mail {
  font-size: 1rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: underline;
  text-underline-offset: 0.15em;
  word-break: break-word;
  transition: color var(--duration-fast) var(--ease-out);
}

.contact-mail:hover {
  color: var(--accent-hover);
}

.contact-note {
  margin: 0;
  font-size: 0.85rem;
}
</style>
