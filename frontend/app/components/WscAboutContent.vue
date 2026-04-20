<template>
  <div class="about-content">
    <!-- h1 staat in de hero; dit blok is het story-gedeelte van de homepage -->
    <h2 id="home-story-title" class="about-content__title font-display">{{ t('about.title') }}</h2>
    <p class="body-text about-content__mission">{{ t('about.missionBody') }}</p>
    <div class="story-grid">
      <div class="story-card">
        <h3 class="story-card__title font-display">{{ t('about.whyTitle') }}</h3>
        <p class="body-text story-card__lead">{{ t('about.whyLead') }}</p>
        <ul class="story-list">
          <li class="story-list__item">{{ t('about.whyPoint1') }}</li>
          <li class="story-list__item">{{ t('about.whyPoint2') }}</li>
          <li class="story-list__item">{{ t('about.whyPoint3') }}</li>
        </ul>
      </div>
      <div class="story-card">
        <h3 class="story-card__title font-display">{{ t('about.whatTitle') }}</h3>
        <ul class="story-list story-list--what">
          <li class="story-list__item">{{ t('about.whatPoint1') }}</li>
          <li class="story-list__item">{{ t('about.whatPoint2') }}</li>
          <li class="story-list__item">{{ t('about.whatPoint3') }}</li>
          <li class="story-list__item">{{ t('about.whatPoint4') }}</li>
        </ul>
      </div>
    </div>
    <div class="story-how">
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
  font-size: clamp(1.35rem, 3.5vw, 1.75rem);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.2;
  margin: 0 0 0.75rem;
  color: var(--text-primary);
}

.about-content__mission {
  margin: 0 0 1.25rem;
  color: var(--text-secondary);
  line-height: 1.65;
  max-width: 52rem;
}

.story-grid {
  display: grid;
  gap: 1.15rem;
  margin: 0 0 1.25rem;
}

@media (min-width: 640px) {
  .story-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.story-card {
  padding: 1rem 1rem 1.05rem;
  border-radius: var(--radius-md);
  background: var(--surface-muted);
  border: 1px solid var(--border-subtle);
}

.story-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  color: var(--text-primary);
  letter-spacing: -0.02em;
}

.story-card__lead {
  margin: 0 0 0.65rem;
  color: var(--text-secondary);
  line-height: 1.6;
  font-size: 0.94rem;
}

.story-list {
  margin: 0;
  padding-left: 1.2rem;
  color: var(--text-secondary);
  line-height: 1.58;
  font-size: 0.92rem;
}

.story-list--what {
  padding-left: 1.15rem;
}

.story-list__item {
  margin: 0.45rem 0;
}

.story-list__item::marker {
  color: var(--accent);
}

.story-how {
  padding: 1rem 1rem 0.85rem;
  border-radius: var(--radius-md);
  border: 1px dashed color-mix(in srgb, var(--accent) 28%, var(--border-subtle));
  background: color-mix(in srgb, var(--accent) 4%, var(--surface-page));
}

.story-how__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.65rem;
  color: var(--text-primary);
  letter-spacing: -0.02em;
}

.story-steps {
  margin: 0 0 0.65rem;
  padding-left: 1.2rem;
  color: var(--text-secondary);
  line-height: 1.58;
  font-size: 0.92rem;
}

.story-steps__item {
  margin: 0.45rem 0;
}

.story-steps__item::marker {
  font-weight: 600;
  color: var(--accent);
}

.story-how__hint {
  margin: 0;
  font-size: 0.88rem;
}
</style>
