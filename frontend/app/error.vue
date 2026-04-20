<template>
  <NuxtLayout name="default">
    <main id="main-content" class="wsc-error">
      <div class="wsc-error__card">
        <p class="wsc-error__code" aria-hidden="true">{{ error.statusCode }}</p>
        <h1 class="wsc-error__title font-display">{{ title }}</h1>
        <p class="wsc-error__message">{{ message }}</p>
        <div class="wsc-error__actions">
          <button type="button" class="wsc-error__btn" @click="goHome">
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
  padding: 2rem 1.25rem 3rem;
}

.wsc-error__card {
  width: 100%;
  max-width: 28rem;
  padding: 1.75rem 1.5rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-subtle);
  background: var(--surface-elevated);
  box-shadow: var(--shadow-sm);
  text-align: center;
}

.wsc-error__code {
  margin: 0 0 0.35rem;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--text-tertiary);
}

.wsc-error__title {
  margin: 0 0 0.65rem;
  font-size: 1.5rem;
  line-height: 1.25;
  color: var(--text-primary);
}

.wsc-error__message {
  margin: 0 0 1.25rem;
  font-size: 0.95rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.wsc-error__actions {
  display: flex;
  justify-content: center;
}

.wsc-error__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2.65rem;
  padding: 0.55rem 1.15rem;
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: var(--radius-md);
  cursor: pointer;
  border: none;
  background: var(--accent);
  color: #fff;
  box-shadow: var(--shadow-sm);
  transition:
    background var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out);
}

.wsc-error__btn:hover {
  background: var(--accent-hover);
}
</style>
