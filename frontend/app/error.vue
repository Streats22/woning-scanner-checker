<template>
  <NuxtLayout name="default">
    <main id="main-content" class="wsc-error">
      <div class="wsc-error__card">
        <BrandMark class="wsc-error__mark" :size="96" />
        <p class="wsc-error__code" aria-hidden="true">{{ error.statusCode }}</p>
        <h1 class="wsc-error__title font-display">{{ title }}</h1>
        <p class="wsc-error__message">{{ message }}</p>
        <div class="wsc-error__actions">
          <button type="button" class="wsc-btn wsc-btn--action" @click="goHome">
            {{ t('faq.backToHome') }}
          </button>
        </div>
      </div>
    </main>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{
  error: NuxtError
}>()

const { t } = useI18n()

const title = computed(() =>
  props.error.statusCode === 404 ? t('errors.notFoundTitle') : t('errors.serverTitle'),
)

const message = computed(() => {
  if (props.error.statusCode === 404) {
    return t('errors.notFound')
  }
  const m = props.error.message?.trim()
  if (m && m.length > 0 && m !== 'Not Found') {
    return m
  }
  return t('errors.generic')
})

useHead(() => ({
  title: title.value,
}))

function goHome() {
  clearError({ redirect: '/' })
}
</script>

<style scoped>
.wsc-error {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 1.25rem 4rem;
}

.wsc-error__card {
  width: 100%;
  max-width: 32rem;
  padding: clamp(2rem, 5vw, 3rem) clamp(1.25rem, 4vw, 2.5rem);
  border-radius: var(--radius-xl);
  background: var(--brand);
  color: var(--on-brand);
  text-align: center;
}

.wsc-error__mark {
  margin: 0 auto 0.75rem;
}

/* Kaart komt in beeld; de radar blijft rustig zoeken */
@media (prefers-reduced-motion: no-preference) {
  .wsc-error__card {
    animation: wsc-error-in 0.6s var(--ease-out) both;
  }

  .wsc-error__mark :deep(.brand-mark__lines) {
    transform-origin: 20px 20px;
    animation: wsc-error-sweep 3.2s linear infinite;
  }
}

@keyframes wsc-error-in {
  from {
    opacity: 0;
    transform: translateY(16px) scale(0.98);
  }
}

@keyframes wsc-error-sweep {
  to {
    transform: rotate(360deg);
  }
}

.wsc-error__code {
  margin: 0;
  font-size: clamp(4rem, 14vw, 6.5rem);
  font-weight: 800;
  line-height: 1;
  letter-spacing: -0.06em;
  color: var(--brand-soft);
}

.wsc-error__title {
  margin: 0.5rem 0 0.65rem;
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.2;
  color: var(--on-brand);
}

.wsc-error__message {
  margin: 0 0 1.5rem;
  line-height: 1.55;
  color: var(--on-brand-muted);
}

.wsc-error__actions {
  display: flex;
  justify-content: center;
}
</style>
