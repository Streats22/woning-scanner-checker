<template>
  <div>
    <div class="hero">
      <Transition name="wsc-locale" mode="out-in">
        <div :key="locale" class="hero__inner">
          <p class="hero__eyebrow font-display hero-reveal hero-reveal--1">{{ t('brand.tagline') }}</p>
          <h1 class="hero__title font-display hero-reveal hero-reveal--2">{{ t('hero.title') }}</h1>
          <p class="hero__sub hero-reveal hero-reveal--3">{{ t('hero.subtitle') }}</p>
          <ul class="hero__pillars hero-reveal hero-reveal--4" :aria-label="t('trust.pillarsLabel')">
            <li v-for="(line, i) in trustPillars" :key="i" class="hero__pillar">
              {{ line }}
            </li>
          </ul>
          <p class="hero__trust hero-reveal hero-reveal--5">{{ t('trust.line') }}</p>
          <div class="hero__cta hero-reveal hero-reveal--6">
            <NuxtLink class="hero__cta-btn" :to="localePath('/check')">{{ t('home.ctaCheck') }}</NuxtLink>
            <a class="hero__cta-secondary" href="#story">{{ t('home.ctaReadStory') }}</a>
          </div>
        </div>
      </Transition>
    </div>

    <Transition name="wsc-locale" mode="out-in">
      <div :key="locale" class="main main--landing">
        <section id="story" class="home-story" aria-labelledby="home-story-title">
          <WscAboutContent />
        </section>

        <p class="home-after-story">
          <NuxtLink class="home-after-story__btn" :to="localePath('/check')">{{ t('home.ctaAfterStory') }}</NuxtLink>
        </p>

        <section class="landing-grid" aria-labelledby="landing-more-title">
          <h2 id="landing-more-title" class="landing-grid__sr-only">{{ t('home.landingMoreTitle') }}</h2>
          <article class="landing-card">
            <h3 class="landing-card__title font-display">{{ t('home.cardCheckTitle') }}</h3>
            <p class="landing-card__body">{{ t('home.cardCheckBody') }}</p>
            <NuxtLink class="landing-card__link" :to="localePath('/check')">{{ t('home.cardCheckLink') }}</NuxtLink>
          </article>
          <article class="landing-card">
            <h3 class="landing-card__title font-display">{{ t('home.cardFaqTitle') }}</h3>
            <p class="landing-card__body">{{ t('home.cardFaqBody') }}</p>
            <NuxtLink class="landing-card__link" :to="localePath('/faq')">{{ t('home.cardFaqLink') }}</NuxtLink>
          </article>
        </section>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
const { t, locale } = useI18n()
const localePath = useLocalePath()

const trustPillarIds = ['pillar1', 'pillar2', 'pillar3'] as const
const trustPillars = computed(() => trustPillarIds.map(id => t(`trust.${id}`)))

useWscSeo({
  title: () => t('meta.homeTitle'),
  description: () => t('meta.homeDescription'),
  path: '/',
})
</script>

<style scoped>
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.hero {
  min-height: clamp(14rem, 36vh, 22rem);
  background: linear-gradient(
    165deg,
    color-mix(in srgb, var(--accent) 8%, var(--surface-page)) 0%,
    var(--surface-page) 45%,
    var(--surface-page) 100%
  );
  border-bottom: 1px solid var(--border-subtle);
  transition: background 0.5s var(--ease-out), border-color 0.4s var(--ease-out);
}

.hero__inner {
  max-width: 960px;
  margin: 0 auto;
  padding: max(2.5rem, env(safe-area-inset-top, 0px)) max(1.25rem, env(safe-area-inset-right, 0px)) 2.25rem
    max(1.25rem, env(safe-area-inset-left, 0px));
}

.hero__eyebrow {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--accent);
  margin: 0 0 0.5rem;
}

.hero__title {
  font-size: clamp(1.5rem, 4vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.2;
  margin: 0 0 0.75rem;
  color: var(--text-primary);
}

.hero__sub {
  font-size: 1.05rem;
  color: var(--text-secondary);
  max-width: 38rem;
  margin: 0 0 1rem;
}

.hero__trust {
  font-size: 0.85rem;
  color: var(--text-tertiary);
  margin: 0 0 1.25rem;
  padding: 0.65rem 0.85rem;
  border-radius: var(--radius-sm);
  background: var(--surface-muted);
  border: 1px solid var(--border-subtle);
  display: inline-block;
}

.hero-reveal {
  opacity: 0;
  animation: wsc-fade-in-up var(--duration-slow) var(--ease-out) forwards;
}

.hero-reveal--1 {
  animation-delay: 0.06s;
}

.hero-reveal--2 {
  animation-delay: 0.14s;
}

.hero-reveal--3 {
  animation-delay: 0.22s;
}

.hero-reveal--4 {
  animation-delay: 0.3s;
}

.hero-reveal--5 {
  animation-delay: 0.38s;
}

.hero-reveal--6 {
  animation-delay: 0.46s;
}

.hero__pillars {
  list-style: none;
  margin: 0 0 0.75rem;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
}

.hero__pillar {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text-secondary);
  padding: 0.4rem 0.7rem;
  border-radius: 999px;
  background: var(--surface-elevated);
  border: 1px solid var(--border-subtle);
  box-shadow: var(--shadow-sm);
  transition:
    transform var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out);
}

@media (prefers-reduced-motion: no-preference) {
  .hero__pillar:nth-child(1) {
    animation: wsc-pillar-in 0.48s var(--ease-out) 0.32s backwards;
  }

  .hero__pillar:nth-child(2) {
    animation: wsc-pillar-in 0.48s var(--ease-out) 0.4s backwards;
  }

  .hero__pillar:nth-child(3) {
    animation: wsc-pillar-in 0.48s var(--ease-out) 0.48s backwards;
  }
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .hero__pillar:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    border-color: color-mix(in srgb, var(--accent) 22%, var(--border-subtle));
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero-reveal {
    opacity: 1;
    animation: none;
    transform: none;
  }
}

.hero__cta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.65rem 1rem;
}

.hero__cta-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.35rem;
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

@media (hover: hover) and (pointer: fine) {
  .hero__cta-btn:hover {
    background: var(--accent-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }
}

.hero__cta-btn:focus-visible {
  outline: 2px solid var(--surface-elevated);
  outline-offset: 2px;
  box-shadow: 0 0 0 4px var(--accent-muted);
}

.hero__cta-secondary {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: underline;
  text-underline-offset: 0.18em;
}

.hero__cta-secondary:hover {
  color: var(--accent-hover);
}

.main--landing {
  flex: 1;
  max-width: 960px;
  margin: 0 auto;
  width: 100%;
  min-width: 0;
  padding:
    1.75rem
    max(1.25rem, env(safe-area-inset-right, 0px))
    max(3rem, env(safe-area-inset-bottom, 0px))
    max(1.25rem, env(safe-area-inset-left, 0px));
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
}

.home-story {
  padding: 1.5rem 1.4rem;
  border-radius: var(--radius-lg);
  border: 1px solid color-mix(in srgb, var(--accent) 14%, var(--border-subtle));
  background: var(--surface-elevated);
  box-shadow: var(--shadow-md);
  scroll-margin-top: 5rem;
}

.home-after-story {
  margin: 0;
  text-align: center;
}

.home-after-story__btn {
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

@media (hover: hover) and (pointer: fine) {
  .home-after-story__btn:hover {
    background: var(--accent-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }
}

.home-after-story__btn:focus-visible {
  outline: 2px solid var(--surface-elevated);
  outline-offset: 2px;
  box-shadow: 0 0 0 4px var(--accent-muted);
}

.landing-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 700px) {
  .landing-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.landing-grid__sr-only {
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

.landing-card {
  padding: 1.15rem 1.25rem 1.25rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-subtle);
  background: var(--surface-elevated);
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-height: 100%;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .landing-card:hover {
    border-color: color-mix(in srgb, var(--accent) 22%, var(--border-subtle));
    box-shadow: var(--shadow-panel-hover);
  }
}

.landing-card__title {
  font-size: 0.98rem;
  font-weight: 600;
  margin: 0;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.landing-card__body {
  margin: 0;
  flex: 1;
  font-size: 0.875rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.landing-card__link {
  display: inline-flex;
  align-items: center;
  margin-top: 0.15rem;
  padding: 0.35rem 0;
  min-height: 44px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--accent);
  text-decoration: none;
  align-self: flex-start;
  touch-action: manipulation;
}

.landing-card__link:hover {
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

@media (prefers-reduced-motion: no-preference) {
  .landing-card {
    animation: wsc-panel-rise 0.55s var(--ease-out) backwards;
  }

  .landing-card:nth-child(1) {
    animation-delay: 0.04s;
  }

  .landing-card:nth-child(2) {
    animation-delay: 0.1s;
  }
}

@media (prefers-reduced-motion: reduce) {
  .landing-card {
    animation: none;
  }
}
</style>
