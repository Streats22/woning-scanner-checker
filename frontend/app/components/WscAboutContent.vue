<template>
  <div class="about-content">
    <!-- h1 staat in de hero; dit blok is het story-gedeelte van de homepage -->
    <h2 id="home-story-title" class="about-content__title font-display">{{ t('about.title') }}</h2>
    <p class="body-text about-content__mission">{{ t('about.missionBody') }}</p>
    <div class="story-grid">
      <div v-reveal="0" class="story-card">
        <h3 class="story-card__title font-display">{{ t('about.whyTitle') }}</h3>
        <p class="body-text story-card__lead">{{ t('about.whyLead') }}</p>
        <ul class="story-list">
          <li class="story-list__item">{{ t('about.whyPoint1') }}</li>
          <li class="story-list__item">{{ t('about.whyPoint2') }}</li>
          <li class="story-list__item">{{ t('about.whyPoint3') }}</li>
        </ul>
      </div>
      <div v-reveal="1" class="story-card">
        <h3 class="story-card__title font-display">{{ t('about.whatTitle') }}</h3>
        <ul class="story-list story-list--what">
          <li class="story-list__item">{{ t('about.whatPoint1') }}</li>
          <li class="story-list__item">{{ t('about.whatPoint2') }}</li>
          <li class="story-list__item">{{ t('about.whatPoint3') }}</li>
          <li class="story-list__item">{{ t('about.whatPoint4') }}</li>
        </ul>
      </div>
    </div>
    <div v-reveal class="story-how">
      <h3 class="story-how__title font-display">{{ t('about.howShortTitle') }}</h3>
      <ol class="story-steps">
        <li class="story-steps__item">{{ t('about.howShortStep1') }}</li>
        <li class="story-steps__item">{{ t('about.howShortStep2') }}</li>
        <li class="story-steps__item">{{ t('about.howShortStep3') }}</li>
      </ol>
      <p class="body-text muted story-how__hint">{{ t('about.howShortHint') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
const { t } = useI18n()
</script>

<style scoped>
.about-content__title {
  margin: 0 0 0.75rem;
  font-size: clamp(1.6rem, 3.5vw, 2.25rem);
  font-weight: 800;
  letter-spacing: -0.035em;
  line-height: 1.1;
  color: var(--text-primary);
}

.about-content__mission {
  margin: 0 0 1.5rem;
  max-width: 52rem;
  font-size: 1.05rem;
  line-height: 1.65;
  color: var(--text-secondary);
}

.story-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr));
  gap: 1rem;
}

.story-card {
  padding: clamp(1.25rem, 3vw, 1.75rem);
  border-radius: var(--radius-lg);
  background: var(--surface-elevated);
}

.story-card__title,
.story-how__title {
  margin: 0 0 0.6rem;
  font-size: 1.2rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.story-card__lead {
  margin: 0 0 0.75rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.story-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.55rem;
}

.story-list__item {
  position: relative;
  padding-left: 1.6rem;
  line-height: 1.5;
  color: var(--text-primary);
}

.story-list__item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.45rem;
  width: 0.65rem;
  height: 0.65rem;
  border-radius: 50%;
  background: var(--action);
}

.story-list--what .story-list__item::before {
  background: var(--accent);
}

.story-how {
  margin-top: 1rem;
  padding: clamp(1.25rem, 3vw, 1.75rem);
  border-radius: var(--radius-lg);
  background: var(--brand);
  color: var(--on-brand);
}

.story-how__title {
  color: var(--on-brand);
}

.story-steps {
  margin: 0 0 0.75rem;
  padding: 0;
  list-style: none;
  counter-reset: step;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
  gap: 0.75rem;
}

.story-steps__item {
  counter-increment: step;
  display: grid;
  grid-template-columns: 2rem 1fr;
  gap: 0.65rem;
  align-items: start;
  line-height: 1.5;
  color: var(--on-brand);
}

.story-steps__item::before {
  content: counter(step);
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand);
  font-weight: 800;
  display: grid;
  place-items: center;
}

.story-how__hint {
  margin: 0;
  font-size: 0.92rem;
  color: var(--on-brand-muted);
}
</style>
