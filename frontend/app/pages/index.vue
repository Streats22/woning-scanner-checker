<template>
  <div>
    <section class="wsc-hero home-hero">
      <Transition name="wsc-locale" mode="out-in">
        <div :key="locale" class="wsc-container home-hero__inner">
          <div class="home-hero__copy">
            <h1 class="home-hero__title font-display hero-reveal hero-reveal--1">{{ t('hero.title') }}</h1>
            <p class="home-hero__sub hero-reveal hero-reveal--2">{{ t('hero.subtitle') }}</p>

            <form class="home-hero__form hero-reveal hero-reveal--3" @submit.prevent="onHeroSubmit">
              <label class="sr-only" for="home-hero-input">{{ t('home.heroInputLabel') }}</label>
              <input
                id="home-hero-input"
                v-model="heroText"
                class="home-hero__input"
                type="text"
                autocomplete="off"
                :placeholder="t('home.heroInputPlaceholder')"
              >
              <button type="submit" class="wsc-btn wsc-btn--action home-hero__submit">
                {{ t('form.submit') }}
              </button>
            </form>
            <p class="home-hero__alt hero-reveal hero-reveal--3">
              {{ t('home.heroScreenshotHint') }}
              <NuxtLink class="home-hero__alt-link" :to="localePath('/check')">{{ t('home.heroScreenshotLink') }}</NuxtLink>
            </p>

            <ul class="home-hero__pillars hero-reveal hero-reveal--4" :aria-label="t('trust.pillarsLabel')">
              <li v-for="(line, i) in trustPillars" :key="i" class="home-hero__pillar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10" /></svg>
                {{ line }}
              </li>
            </ul>
          </div>

          <figure class="home-sample hero-reveal hero-reveal--5" :aria-label="t('home.sampleAria')">
            <div class="home-sample__screen">
              <figcaption class="home-sample__caption">{{ t('home.sampleCaption') }}</figcaption>
              <div class="home-sample__card home-sample__card--score">
                <ScoreGauge :score="80" :aria-label="t('home.sampleScoreAria')" :width="200" />
                <p class="home-sample__risk">{{ t('risk.high') }}</p>
              </div>
              <ul class="home-sample__card home-sample__rows">
                <li class="home-sample__row"><span>{{ t('home.sampleRow1') }}</span><strong class="home-sample__pts">+40</strong></li>
                <li class="home-sample__row"><span>{{ t('home.sampleRow2') }}</span><strong class="home-sample__pts">+30</strong></li>
                <li class="home-sample__row"><span>{{ t('home.sampleRow3') }}</span><strong class="home-sample__pts home-sample__pts--mid">+10</strong></li>
              </ul>
            </div>
          </figure>
        </div>
      </Transition>
    </section>

    <Transition name="wsc-locale" mode="out-in">
      <div :key="locale" class="wsc-container home-body">
        <section id="story" class="home-story" aria-labelledby="home-story-title">
          <WscAboutContent />
          <p class="home-story__trust">{{ t('trust.line') }}</p>
        </section>

        <section class="landing-grid" aria-labelledby="landing-more-title">
          <h2 id="landing-more-title" class="sr-only">{{ t('home.landingMoreTitle') }}</h2>
          <NuxtLink v-reveal="0" class="landing-card" :to="localePath('/check')">
            <span class="landing-card__icon landing-card__icon--check" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7" /><path d="M20 20l-4-4" /></svg>
            </span>
            <h3 class="landing-card__title font-display">{{ t('home.cardCheckTitle') }}</h3>
            <p class="landing-card__body">{{ t('home.cardCheckBody') }}</p>
            <span class="landing-card__link">{{ t('home.cardCheckLink') }} →</span>
          </NuxtLink>
          <NuxtLink v-reveal="1" class="landing-card" :to="localePath('/huursites')">
            <span class="landing-card__icon landing-card__icon--sites" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-7 9 7" /><path d="M5 10v10h14V10" /></svg>
            </span>
            <h3 class="landing-card__title font-display">{{ t('home.cardSitesTitle') }}</h3>
            <p class="landing-card__body">{{ t('home.cardSitesBody') }}</p>
            <span class="landing-card__link">{{ t('home.cardSitesLink') }} →</span>
          </NuxtLink>
          <NuxtLink v-reveal="2" class="landing-card" :to="localePath('/faq')">
            <span class="landing-card__icon landing-card__icon--faq" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.3-1 .9-1 1.6v.6M12 17v.5" /></svg>
            </span>
            <h3 class="landing-card__title font-display">{{ t('home.cardFaqTitle') }}</h3>
            <p class="landing-card__body">{{ t('home.cardFaqBody') }}</p>
            <span class="landing-card__link">{{ t('home.cardFaqLink') }} →</span>
          </NuxtLink>
        </section>

        <section v-reveal class="home-cta">
          <p class="home-cta__text font-display">{{ t('hero.title') }}</p>
          <NuxtLink class="wsc-btn wsc-btn--action" :to="localePath('/check')">{{ t('home.ctaAfterStory') }}</NuxtLink>
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

const heroText = ref('')

/** Startpagina-invoer: door naar /check met de tekst; daar wordt direct geanalyseerd. */
function onHeroSubmit() {
  const text = heroText.value.trim()
  if (!text) {
    void navigateTo(localePath('/check'))
    return
  }
  void navigateTo({ path: localePath('/check'), query: { text, run: '1' } })
}

useWscSeo({
  title: () => t('meta.homeTitle'),
  description: () => t('meta.homeDescription'),
  path: '/',
})

useHead(() => ({
  meta: [
    { name: 'keywords', content: t('meta.keywords') },
  ],
}))
</script>

<style scoped>
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.home-hero__inner {
  padding-top: clamp(1.5rem, 4vw, 3rem);
  padding-bottom: clamp(3rem, 7vw, 5rem);
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 3rem 3.5rem;
}

.home-hero__copy {
  flex: 999 1 30rem;
  min-width: 0;
}

.home-hero__title {
  margin: 0;
  font-size: clamp(2.35rem, 6.2vw, 4.5rem);
  font-weight: 800;
  line-height: 0.98;
  letter-spacing: -0.045em;
  color: var(--on-brand);
}

.home-hero__sub {
  margin: 1.25rem 0 0;
  max-width: 36rem;
  font-size: clamp(1.05rem, 1.8vw, 1.25rem);
  line-height: 1.5;
  color: var(--on-brand-muted);
}

.home-hero__form {
  margin-top: 2rem;
  max-width: 38rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.home-hero__input {
  flex: 999 1 16rem;
  min-width: 0;
  min-height: 58px;
  padding: 0 1.1rem;
  border: 0;
  border-radius: var(--radius-md);
  background: #ffffff;
  color: #0e2a21;
  font: inherit;
  font-size: 1.05rem;
  outline: 3px solid transparent;
  outline-offset: 2px;
  transition:
    outline-color var(--duration-normal) var(--ease-out),
    box-shadow var(--duration-normal) var(--ease-out);
}

.home-hero__input:focus {
  box-shadow: 0 0 0 6px rgba(255, 122, 89, 0.18);
}

.home-hero__input::placeholder {
  color: #5e7369;
}

.home-hero__input:focus-visible {
  outline: 3px solid var(--action);
  outline-offset: 2px;
}

.home-hero__submit {
  flex: 1 1 auto;
  min-height: 58px;
  font-size: 1.05rem;
}

.home-hero__submit:focus-visible {
  outline-color: var(--brand-soft);
}

.home-hero__alt {
  margin: 0.85rem 0 0;
  font-size: 0.95rem;
  color: var(--on-brand-muted);
}

.home-hero__alt-link {
  color: var(--brand-soft);
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 0.18em;
  text-decoration-color: rgba(200, 242, 220, 0.5);
}

.home-hero__alt-link:hover {
  color: var(--on-brand);
  text-decoration-color: currentColor;
}

.home-hero__pillars {
  margin: 2rem 0 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.home-hero__pillar {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.9rem;
  border-radius: 999px;
  background: var(--on-brand-line);
  color: #eaf4ef;
  font-size: 0.92rem;
  font-weight: 600;
}

.home-hero__pillar svg {
  color: #8fe3b8;
  flex-shrink: 0;
}

/* Voorbeeld-telefoon */
@media (prefers-reduced-motion: no-preference) {
  .home-sample__screen {
    animation: home-sample-float 6s ease-in-out 1.2s infinite;
  }

  .home-sample__row {
    animation: home-sample-row 0.5s var(--ease-out) both;
  }

  .home-sample__row:nth-child(1) { animation-delay: 0.9s; }
  .home-sample__row:nth-child(2) { animation-delay: 1.05s; }
  .home-sample__row:nth-child(3) { animation-delay: 1.2s; }

  .home-sample__pts {
    display: inline-block;
    animation: home-sample-pop 0.45s var(--ease-spring) both;
    animation-delay: inherit;
  }
}

@keyframes home-sample-float {
  50% {
    translate: 0 -8px;
  }
}

@keyframes home-sample-row {
  from {
    opacity: 0;
    translate: 18px 0;
  }
}

@keyframes home-sample-pop {
  from {
    scale: 0.4;
    opacity: 0;
  }
}

.home-sample {
  flex: 0 0 auto;
  width: min(20.5rem, 100%);
  margin: 0 auto;
  padding: 12px;
  border-radius: 48px;
  background: #06140f;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.35);
}

.home-sample__screen {
  border-radius: 38px;
  background: #f3f7f4;
  color: #0e2a21;
  padding: 1.6rem 1.1rem 1.1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.home-sample__caption {
  margin: 0;
  text-align: center;
  font-size: 0.85rem;
  color: #4c6159;
}

.home-sample__card {
  background: #ffffff;
  border-radius: 24px;
}

.home-sample__card--score {
  padding: 1.1rem 0.85rem 1rem;
  text-align: center;
}

.home-sample__card--score :deep(.score-gauge) {
  margin: 0 auto;
  color: #0e2a21;
}

.home-sample__card--score :deep(.score-gauge__track) {
  stroke: #e2ebe6;
}

.home-sample__card--score :deep(.score-gauge__fill) {
  stroke: #e5482a;
}

.home-sample__risk {
  margin: 0.35rem 0 0;
  font-size: 1.3rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #a12e18;
}

.home-sample__rows {
  margin: 0;
  padding: 0.25rem 1rem;
  list-style: none;
}

.home-sample__row {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.7rem 0;
  font-size: 0.92rem;
}

.home-sample__row + .home-sample__row {
  border-top: 1px solid #e2ebe6;
}

.home-sample__pts {
  color: #a12e18;
}

.home-sample__pts--mid {
  color: #7a4f00;
}

/* Inhoud */
.home-body {
  padding-top: clamp(3.5rem, 8vw, 5.5rem);
  padding-bottom: clamp(3.5rem, 8vw, 6rem);
  display: flex;
  flex-direction: column;
  gap: clamp(3rem, 6vw, 4.5rem);
}

.home-story {
  scroll-margin-top: 6rem;
}

.home-story__trust {
  margin: 1.5rem 0 0;
  max-width: 52rem;
  font-size: 0.92rem;
  line-height: 1.6;
  color: var(--text-tertiary);
}

.landing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(17rem, 1fr));
  gap: 1.1rem;
}

.landing-card {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  padding: 1.75rem;
  border-radius: var(--radius-lg);
  background: var(--surface-page);
  box-shadow: inset 0 0 0 1px var(--border-subtle);
  color: var(--text-primary);
  text-decoration: none;
  transition:
    box-shadow var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

.landing-card:hover {
  color: var(--text-primary);
  text-decoration: none;
  background: var(--surface-elevated);
  box-shadow: inset 0 0 0 1.5px var(--border-strong);
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .landing-card:hover {
    transform: translateY(-4px);
  }

  .landing-card:hover .landing-card__icon {
    transform: rotate(-6deg) scale(1.06);
  }
}

.landing-card:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
  border-radius: var(--radius-lg);
}

.landing-card__icon {
  width: 48px;
  height: 48px;
  transition: transform var(--duration-normal) var(--ease-spring);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
}

.landing-card__icon--check {
  background: var(--risk-high-bg);
  color: var(--risk-high);
}

.landing-card__icon--sites {
  background: var(--brand-soft);
  color: var(--brand);
}

.landing-card__icon--faq {
  background: var(--risk-mid-bg);
  color: var(--risk-mid);
}

.landing-card__title {
  margin: 0.35rem 0 0;
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.025em;
  line-height: 1.2;
}

.landing-card__body {
  margin: 0;
  font-size: 1rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.landing-card__link {
  margin-top: auto;
  padding-top: 0.5rem;
  font-weight: 700;
  color: var(--accent);
}

.home-cta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem 2rem;
  padding: clamp(1.75rem, 4vw, 2.5rem);
  border-radius: var(--radius-xl);
  background: var(--brand);
  color: var(--on-brand);
}

.home-cta__text {
  flex: 999 1 22rem;
  margin: 0;
  font-size: clamp(1.4rem, 3vw, 2rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.035em;
}

.home-cta .wsc-btn {
  flex: 1 1 auto;
  max-width: 20rem;
}

.home-cta .wsc-btn:focus-visible {
  outline-color: var(--brand-soft);
}

/* Inkomende animatie hero */
.hero-reveal {
  opacity: 0;
  animation: wsc-fade-in-up var(--duration-slow) var(--ease-out) forwards;
}

.hero-reveal--1 {
  animation-delay: 0.04s;
}

.hero-reveal--2 {
  animation-delay: 0.1s;
}

.hero-reveal--3 {
  animation-delay: 0.16s;
}

.hero-reveal--4 {
  animation-delay: 0.22s;
}

.hero-reveal--5 {
  animation-delay: 0.28s;
}

@media (prefers-reduced-motion: reduce) {
  .hero-reveal {
    opacity: 1;
    animation: none;
  }
}
</style>
