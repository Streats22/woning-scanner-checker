<template>
  <div class="shell">
    <NuxtRouteAnnouncer />
    <header class="topbar">
      <div class="topbar__inner">
        <div class="brand">
          <span class="brand__mark" aria-hidden="true">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="40" height="40" rx="12" class="brand__mark-bg" />
              <path d="M20 10L12 14v8c0 5 3.5 9.5 8 11 4.5-1.5 8-6 8-11v-8l-8-4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="none" />
              <path d="M17 20l2 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <div class="brand__text">
            <span class="brand__name font-display">{{ t('brand.name') }}</span>
            <span class="brand__badge">{{ t('brand.badge') }}</span>
          </div>
        </div>

        <div class="topbar__actions">
          <div
            class="seg seg--2"
            :class="locale === 'nl' ? 'seg--i0' : 'seg--i1'"
            role="group"
            :aria-label="t('header.language')"
          >
            <span class="seg__pill" aria-hidden="true" />
            <button
              type="button"
              class="seg__btn"
              :class="{ 'seg__btn--active': locale === 'nl' }"
              @click="setLocale('nl')"
            >
              NL
            </button>
            <button
              type="button"
              class="seg__btn"
              :class="{ 'seg__btn--active': locale === 'en' }"
              @click="setLocale('en')"
            >
              EN
            </button>
          </div>

          <div
            class="seg seg--3"
            :class="'seg--i' + themeSegIndex"
            role="group"
            :aria-label="t('header.themeLight') + ' / ' + t('header.themeDark')"
          >
            <span class="seg__pill" aria-hidden="true" />
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'light' }"
              :title="t('header.themeLight')"
              @click="colorMode.preference = 'light'"
            >
              <span class="seg__glyph" aria-hidden="true">☀</span>
            </button>
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'system' }"
              :title="t('header.themeSystem')"
              @click="colorMode.preference = 'system'"
            >
              <span class="seg__glyph" aria-hidden="true">◐</span>
            </button>
            <button
              type="button"
              class="seg__btn seg__btn--icon"
              :class="{ 'seg__btn--active': colorMode.preference === 'dark' }"
              :title="t('header.themeDark')"
              @click="colorMode.preference = 'dark'"
            >
              <span class="seg__glyph" aria-hidden="true">☾</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <div class="hero">
      <Transition name="wsc-locale" mode="out-in">
        <div :key="locale" class="hero__inner">
          <p class="hero__eyebrow font-display hero-reveal hero-reveal--1">{{ t('brand.tagline') }}</p>
          <h1 class="hero__title font-display hero-reveal hero-reveal--2">{{ t('hero.title') }}</h1>
          <p class="hero__sub hero-reveal hero-reveal--3">{{ t('hero.subtitle') }}</p>
          <p class="hero__trust hero-reveal hero-reveal--4">{{ t('trust.line') }}</p>
        </div>
      </Transition>
    </div>

    <main class="main">
      <div class="panel panel--form">
        <form class="form" @submit.prevent="onSubmit">
          <label class="label" for="text">{{ t('form.label') }}</label>
          <textarea
            id="text"
            v-model="text"
            class="textarea"
            required
            rows="10"
            :placeholder="t('form.placeholder')"
          />
          <p class="form__hint">{{ t('form.hint') }}</p>
          <button type="submit" class="btn" :class="{ 'btn--pending': pending }" :disabled="pending">
            <span class="btn__spinner" aria-hidden="true" />
            <span class="btn__label">{{ pending ? t('form.submitting') : t('form.submit') }}</span>
          </button>
          <Transition name="wsc-alert">
            <p v-if="errorMsg" class="form__err" role="alert">{{ errorMsg }}</p>
          </Transition>
        </form>
      </div>

      <Transition name="wsc-result">
        <section v-if="result" ref="resultEl" class="panel panel--result" tabindex="-1">
          <div class="score-block">
            <div class="score-block__num" :key="result.score">{{ result.score }}</div>
            <div class="score-block__meta">
              <span class="score-block__label">{{ t('result.scoreLabel') }}</span>
              <span class="pill pill--pop" :class="scorePillClass(result.score)">{{ riskLabel(result.score) }}</span>
              <span v-if="result.llm_used" class="meta-badge">{{ t('result.llmBadge') }}</span>
            </div>
          </div>

          <p v-if="result.link_assessment" class="link-assessment body-text">{{ result.link_assessment }}</p>

          <p v-if="result.rule_score != null && result.rule_score !== result.score" class="rule-note body-text">
            {{ t('result.ruleEngine') }}: <strong>{{ result.rule_score }}</strong> — {{ t('result.finalScore') }}:
            <strong>{{ result.score }}</strong>.
          </p>

          <template v-if="result.methodology">
            <h2 class="section-title font-display">{{ t('result.methodology') }}</h2>
            <p class="body-text muted result-block">{{ result.methodology }}</p>
          </template>

          <template v-if="result.market_context">
            <h2 class="section-title font-display">{{ t('result.marketContext') }}</h2>
            <p class="body-text result-block">{{ result.market_context }}</p>
          </template>

          <template v-if="result.risk_breakdown?.length">
            <h2 class="section-title font-display">{{ t('result.riskBreakdown') }}</h2>
            <ul class="risk-list">
              <li
                v-for="(row, i) in result.risk_breakdown"
                :key="i"
                class="risk-list__item"
              >
                <span class="risk-list__head">
                  <span class="risk-list__cat">{{ row.category }}</span>
                  <span class="risk-list__pts">{{ t('result.riskPoints', { n: row.points }) }}</span>
                </span>
                <span v-if="row.detail" class="risk-list__detail">{{ row.detail }}</span>
              </li>
            </ul>
          </template>

          <h2 class="section-title font-display">{{ t('result.flags') }}</h2>
          <ul v-if="result.flags.length" class="flag-list">
            <li
              v-for="(f, i) in result.flags"
              :key="i"
              class="flag-list__item"
              :style="{ '--i': String(Math.min(i, 14)) }"
            >
              {{ f }}
            </li>
          </ul>
          <p v-else class="muted">{{ t('result.flagsEmpty') }}</p>

          <h2 class="section-title font-display">{{ t('result.market') }}</h2>
          <p class="body-text muted">
            {{ t('result.marketLine', { avg: result.market.average }) }}
            <template v-if="result.market.difference_percent != null">
              {{ ' ' + t('result.marketDiff', { pct: result.market.difference_percent }) }}
            </template>
            <template v-else>
              {{ ' ' + t('result.marketNoPrice') }}
            </template>
          </p>

          <template v-if="result.recommendations?.length">
            <h2 class="section-title font-display">{{ t('result.recommendations') }}</h2>
            <ul class="bullet-list">
              <li v-for="(line, i) in result.recommendations" :key="i" class="bullet-list__item">
                {{ line }}
              </li>
            </ul>
          </template>

          <template v-if="result.what_to_verify?.length">
            <h2 class="section-title font-display">{{ t('result.whatToVerify') }}</h2>
            <ul class="bullet-list bullet-list--check">
              <li v-for="(line, i) in result.what_to_verify" :key="i" class="bullet-list__item">
                {{ line }}
              </li>
            </ul>
          </template>

          <h2 class="section-title font-display">{{ t('result.summary') }}</h2>
          <p
            v-if="result.summary_short && result.summary_short !== result.summary"
            class="body-text summary-lead"
          >
            {{ result.summary_short }}
          </p>
          <p v-if="result.narrative" class="body-text narrative">{{ result.narrative }}</p>
          <p class="body-text summary">{{ result.summary }}</p>

          <div v-if="result.report_pdf_url || result.report_url" class="share">
            <span class="share__label">{{ t('result.share') }}</span>
            <div class="share__actions">
              <a
                v-if="result.report_pdf_url"
                class="share__btn"
                :href="result.report_pdf_url"
                target="_blank"
                rel="noopener"
              >{{ t('result.downloadPdf') }}</a>
              <a
                v-if="result.report_url"
                class="share__link"
                :href="result.report_url"
                target="_blank"
                rel="noopener"
              >{{ t('result.webReport') }}</a>
            </div>
          </div>
        </section>
      </Transition>
    </main>

    <footer class="footer">
      <p class="footer__text">{{ t('footer.note') }}</p>
    </footer>
  </div>
</template>

<script setup lang="ts">
type RiskBreakdownRow = {
  category: string
  points: number
  detail?: string
}

type AnalyzeResponse = {
  score: number
  rule_score?: number
  flags: string[]
  market: { average: number; difference_percent: number | null }
  summary: string
  summary_short?: string | null
  narrative?: string | null
  llm_used?: boolean
  link_assessment?: string | null
  recommendations?: string[]
  what_to_verify?: string[]
  risk_breakdown?: RiskBreakdownRow[]
  methodology?: string | null
  market_context?: string | null
  id?: number
  report_url?: string
  report_pdf_url?: string
  report_slug?: string
}

const { t, locale, setLocale } = useI18n()
const colorMode = useColorMode()
const config = useRuntimeConfig()

const themeSegIndex = computed(() => {
  const p = colorMode.preference
  if (p === 'light')
    return 0
  if (p === 'system')
    return 1
  return 2
})

const text = ref('')
const pending = ref(false)
const errorMsg = ref('')
const result = ref<AnalyzeResponse | null>(null)
const resultEl = ref<HTMLElement | null>(null)

watch(result, async (r) => {
  if (!r || !import.meta.client)
    return
  await nextTick()
  const el = resultEl.value
  if (!el)
    return
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  el.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'nearest' })
  el.focus({ preventScroll: true })
})

useHead(() => ({
  title: t('meta.title'),
  htmlAttrs: {
    lang: locale.value,
  },
}))

function apiUrl(path: string): string {
  const p = path.startsWith('/') ? path : `/${path}`
  const raw = config.public.apiBase
  const fallback = config.public.apiBaseDefault
  const effective =
    raw !== undefined && raw !== null && String(raw).trim() !== ''
      ? String(raw).trim()
      : (fallback !== undefined && fallback !== null && String(fallback).trim() !== ''
          ? String(fallback).trim()
          : 'http://woning-scam-checker.test')
  return `${effective.replace(/\/$/, '')}${p}`
}

function scorePillClass(n: number) {
  if (n <= 30) return 'pill--low'
  if (n <= 60) return 'pill--mid'
  return 'pill--high'
}

function riskLabel(n: number) {
  if (n <= 30) return t('risk.low')
  if (n <= 60) return t('risk.medium')
  return t('risk.high')
}

async function onSubmit() {
  errorMsg.value = ''
  result.value = null
  pending.value = true
  try {
    const data = await $fetch<AnalyzeResponse>(apiUrl('/api/analyze'), {
      method: 'POST',
      body: { text: text.value },
    })
    result.value = data
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string; errors?: { text?: string[] } }; message?: string }
    const fromValidation = err?.data?.errors?.text?.[0]
    errorMsg.value =
      fromValidation
      || err?.data?.message
      || err?.message
      || t('errors.generic')
  }
  finally {
    pending.value = false
  }
}
</script>

<style scoped>
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 40;
  background: color-mix(in srgb, var(--surface-elevated) 92%, transparent);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-subtle);
  box-shadow: var(--shadow-sm);
  animation: wsc-fade-in var(--duration-normal) var(--ease-out) backwards;
  transition:
    background 0.45s var(--ease-out),
    border-color 0.4s var(--ease-out),
    box-shadow 0.45s var(--ease-out);
}

.topbar__inner {
  max-width: 960px;
  margin: 0 auto;
  padding: 0.875rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.brand__mark {
  flex-shrink: 0;
  color: var(--accent);
}

.brand__mark-bg {
  fill: var(--accent-muted);
}

.brand__text {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  min-width: 0;
}

.brand__name {
  font-weight: 700;
  font-size: 1.05rem;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.brand__badge {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--accent);
}

.topbar__actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.seg {
  position: relative;
  display: inline-flex;
  align-items: stretch;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-subtle);
  background: var(--surface-muted);
  padding: 3px;
  gap: 2px;
}

.seg__pill {
  position: absolute;
  top: 3px;
  bottom: 3px;
  left: 3px;
  width: calc(50% - 3px);
  border-radius: calc(var(--radius-sm) - 2px);
  background: var(--surface-elevated);
  box-shadow:
    inset 0 0 0 1px var(--border-strong),
    0 1px 3px color-mix(in srgb, var(--text-primary) 8%, transparent);
  z-index: 0;
  pointer-events: none;
  transition:
    transform 0.44s var(--ease-spring),
    box-shadow 0.4s var(--ease-out);
  will-change: transform;
}

.seg--2.seg--i1 .seg__pill {
  transform: translateX(calc(100% + 2px));
}

.seg--3 .seg__pill {
  width: calc(33.333% - 2.5px);
}

.seg--3.seg--i1 .seg__pill {
  transform: translateX(calc(100% + 2px));
}

.seg--3.seg--i2 .seg__pill {
  transform: translateX(calc(200% + 4px));
}

.seg__btn {
  position: relative;
  z-index: 1;
  border: none;
  background: transparent;
  color: var(--text-secondary);
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.4rem 0.65rem;
  cursor: pointer;
  line-height: 1.2;
  flex: 1 1 0;
  min-width: 0;
  transition:
    color var(--duration-fast) var(--ease-out),
    transform 0.2s var(--ease-out);
}

.seg__btn--icon {
  flex: 1 1 0;
  min-width: 2.25rem;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.seg__glyph {
  display: inline-block;
  transition: transform 0.38s var(--ease-spring);
  transform-origin: center;
}

.seg__btn--active .seg__glyph {
  transform: scale(1.12);
}

.seg__btn:active:not(:disabled) {
  transform: scale(0.96);
}

.seg__btn:hover:not(.seg__btn--active) {
  color: var(--text-primary);
}

.seg__btn--active {
  color: var(--accent);
  font-weight: 700;
}

.wsc-locale-enter-active,
.wsc-locale-leave-active {
  transition:
    opacity var(--duration-normal) var(--ease-out),
    transform var(--duration-slow) var(--ease-out);
}

.wsc-locale-enter-from {
  opacity: 0;
  transform: translateY(0.65rem);
}

.wsc-locale-leave-to {
  opacity: 0;
  transform: translateY(-0.4rem);
}

@media (prefers-reduced-motion: reduce) {
  .seg__pill {
    transition: none;
  }

  .seg__btn--active .seg__glyph {
    transform: none;
  }

  .wsc-locale-enter-active,
  .wsc-locale-leave-active {
    transition: opacity 0.12s ease;
  }

  .wsc-locale-enter-from,
  .wsc-locale-leave-to {
    transform: none;
  }
}

.hero {
  min-height: clamp(13rem, 32vh, 19rem);
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
  padding: 2.5rem 1.25rem 2rem;
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
  margin: 0;
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

@media (prefers-reduced-motion: reduce) {
  .hero-reveal {
    opacity: 1;
    animation: none;
    transform: none;
  }
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

.panel {
  background: var(--surface-elevated);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-subtle);
  box-shadow: var(--shadow-md);
  padding: 1.5rem 1.35rem;
  transition:
    box-shadow var(--duration-normal) var(--ease-out),
    border-color var(--duration-normal) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

@media (prefers-reduced-motion: no-preference) {
  .panel--form {
    animation: wsc-fade-in-up 0.55s var(--ease-out) 0.1s backwards;
  }
}

.panel--result {
  border-color: color-mix(in srgb, var(--accent) 25%, var(--border-subtle));
}

.panel--result:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.textarea {
  width: 100%;
  min-height: 11rem;
  padding: 0.85rem 1rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-strong);
  background: var(--surface-page);
  color: var(--text-primary);
  font-size: 0.9375rem;
  line-height: 1.5;
  resize: vertical;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.textarea:hover {
  border-color: color-mix(in srgb, var(--accent) 35%, var(--border-strong));
}

.textarea:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-muted);
}

.form__hint {
  font-size: 0.8rem;
  color: var(--text-tertiary);
  margin: 0;
}

.btn {
  position: relative;
  align-self: flex-start;
  margin-top: 0.35rem;
  padding: 0.7rem 1.35rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #fff;
  background: var(--accent);
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  min-height: 2.75rem;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    opacity var(--duration-fast) var(--ease-out);
}

.btn:hover:not(:disabled) {
  background: var(--accent-hover);
  box-shadow: var(--shadow-md);
}

.btn:active:not(:disabled) {
  transform: translateY(1px) scale(0.99);
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn__spinner {
  position: absolute;
  left: 1rem;
  width: 1.05rem;
  height: 1.05rem;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top-color: #fff;
  border-radius: 50%;
  opacity: 0;
  transform: scale(0.85);
  pointer-events: none;
  transition:
    opacity var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.btn--pending .btn__spinner {
  opacity: 1;
  transform: scale(1);
  animation: wsc-spin 0.7s linear infinite;
}

.btn--pending {
  padding-left: 2.5rem;
}

.btn__label {
  position: relative;
}

@media (prefers-reduced-motion: reduce) {
  .btn__spinner {
    animation: none !important;
    border-top-color: rgba(255, 255, 255, 0.85);
  }
}

.form__err {
  color: var(--err);
  font-size: 0.875rem;
  margin: 0.25rem 0 0;
}

.wsc-result-enter-active,
.wsc-result-leave-active {
  transition:
    opacity var(--duration-slow) var(--ease-out),
    transform var(--duration-slow) var(--ease-out);
}

.wsc-result-enter-from {
  opacity: 0;
  transform: translateY(0.75rem);
}

.wsc-result-leave-to {
  opacity: 0;
  transform: translateY(-0.35rem);
}

.wsc-alert-enter-active,
.wsc-alert-leave-active {
  transition:
    opacity var(--duration-normal) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

.wsc-alert-enter-from,
.wsc-alert-leave-to {
  opacity: 0;
  transform: translateY(-0.35rem);
}

@media (prefers-reduced-motion: reduce) {
  .wsc-result-enter-from,
  .wsc-result-leave-to,
  .wsc-alert-enter-from,
  .wsc-alert-leave-to {
    transform: none;
  }
}

.score-block {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
  padding-bottom: 1rem;
  margin-bottom: 0.5rem;
  border-bottom: 1px solid var(--border-subtle);
}

.score-block__num {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -0.04em;
  font-variant-numeric: tabular-nums;
  color: var(--text-primary);
  animation: wsc-fade-in-up 0.55s var(--ease-out) 0.06s backwards;
}

@media (prefers-reduced-motion: reduce) {
  .score-block__num {
    animation: none;
  }
}

.score-block__meta {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding-bottom: 0.2rem;
}

.score-block__label {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.pill {
  display: inline-block;
  width: fit-content;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.pill--low {
  background: var(--risk-low-bg);
  color: var(--risk-low);
}

.pill--mid {
  background: var(--risk-mid-bg);
  color: var(--risk-mid);
}

.pill--high {
  background: var(--risk-high-bg);
  color: var(--risk-high);
}

.meta-badge {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--accent);
  padding: 0.25rem 0.55rem;
  border-radius: var(--radius-sm);
  background: var(--accent-muted);
  width: fit-content;
}

.link-assessment {
  margin: 0 0 0.35rem;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-md);
  background: var(--surface-muted);
  border: 1px solid var(--border-subtle);
  color: var(--text-secondary);
  font-size: 0.9rem;
  line-height: 1.55;
}

.rule-note {
  margin: 0 0 0.75rem;
  padding: 0.65rem 0.85rem;
  border-radius: var(--radius-sm);
  background: color-mix(in srgb, var(--accent-muted) 55%, var(--surface-muted));
  border: 1px solid var(--border-subtle);
  color: var(--text-secondary);
  font-size: 0.85rem;
}

.result-block {
  margin: 0 0 0.25rem;
}

.risk-list {
  margin: 0 0 0.5rem;
  padding: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.risk-list__item {
  padding: 0.65rem 0.85rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: var(--surface-muted);
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.risk-list__head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.risk-list__cat {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.88rem;
}

.risk-list__pts {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--accent);
  font-variant-numeric: tabular-nums;
}

.risk-list__detail {
  font-size: 0.85rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.bullet-list {
  margin: 0;
  padding-left: 1.15rem;
}

.bullet-list__item {
  margin: 0.45rem 0;
  color: var(--text-secondary);
  line-height: 1.55;
}

.bullet-list--check {
  list-style: none;
  padding-left: 0;
}

.bullet-list--check .bullet-list__item {
  position: relative;
  padding-left: 1.35rem;
}

.bullet-list--check .bullet-list__item::before {
  content: '✓';
  position: absolute;
  left: 0;
  color: var(--accent);
  font-weight: 700;
  font-size: 0.85rem;
}

.summary-lead {
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.65rem;
}

.narrative {
  margin: 0 0 0.85rem;
  color: var(--text-secondary);
}

.pill--pop {
  animation: wsc-scale-in 0.5s var(--ease-spring) 0.18s backwards;
}

@media (prefers-reduced-motion: reduce) {
  .pill--pop {
    animation: none;
  }
}

.section-title {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 1.35rem 0 0.5rem;
  color: var(--text-primary);
}

.section-title:first-of-type {
  margin-top: 0.25rem;
}

.flag-list {
  margin: 0;
  padding-left: 1.2rem;
}

.flag-list__item {
  margin: 0.4rem 0;
  color: var(--text-secondary);
  animation: wsc-fade-in-up 0.42s var(--ease-out) backwards;
  animation-delay: calc(0.12s + var(--i, 0) * 42ms);
}

@media (prefers-reduced-motion: reduce) {
  .flag-list__item {
    animation: none;
  }
}

.muted {
  color: var(--text-secondary);
  font-size: 0.9375rem;
}

.body-text {
  font-size: 0.9375rem;
  line-height: 1.65;
}

.summary {
  color: var(--text-primary);
  white-space: pre-line;
}

.share {
  margin-top: 1.25rem;
  padding-top: 1.1rem;
  border-top: 1px solid var(--border-subtle);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.share__label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.share__actions {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.5rem;
}

.share__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.55rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #fff;
  background: var(--accent);
  border-radius: var(--radius-md);
  text-decoration: none;
  box-shadow: var(--shadow-sm);
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.share__btn:hover {
  background: var(--accent-hover);
}

.share__btn:active {
  transform: translateY(1px);
}

.share__link {
  font-size: 0.85rem;
  color: var(--accent);
  text-decoration: underline;
  text-underline-offset: 0.2em;
  word-break: break-word;
  transition:
    color var(--duration-fast) var(--ease-out),
    text-underline-offset var(--duration-fast) var(--ease-out);
}

.share__link:hover {
  text-decoration-thickness: 2px;
}

.footer {
  margin-top: auto;
  padding: 1.5rem 1.25rem 2rem;
  border-top: 1px solid var(--border-subtle);
  background: var(--surface-muted);
  animation: wsc-fade-in var(--duration-slow) var(--ease-out) 0.25s backwards;
}

@media (prefers-reduced-motion: reduce) {
  .footer {
    animation: none;
  }
}

.footer__text {
  max-width: 960px;
  margin: 0 auto;
  font-size: 0.8rem;
  color: var(--text-tertiary);
  text-align: center;
  line-height: 1.5;
}
</style>
