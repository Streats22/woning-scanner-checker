<template>
  <div>
    <Transition name="wsc-locale" mode="out-in">
      <div :key="locale" class="main main--check">
      <section class="wsc-hero check-hero" aria-labelledby="check-page-title">
        <div class="wsc-container wsc-hero__inner">
          <Transition name="wsc-nav-back" appear>
            <p class="check-nav">
              <NuxtLink class="check-nav__link wsc-back-link wsc-back-link--on-brand" :to="localePath('/')">
                <span class="wsc-back-link__arrow" aria-hidden="true">←</span>
                <span class="check-nav__text">{{ t('check.backHome') }}</span>
              </NuxtLink>
            </p>
          </Transition>
          <h1 id="check-page-title" class="wsc-hero__title">{{ t('check.pageTitle') }}</h1>
          <p class="wsc-hero__lead">{{ t('check.pageLead') }}</p>
        </div>
      </section>

      <div class="wsc-container check-body">
      <div class="check-layout">

      <div class="panel panel--form">
        <form
          class="form"
          method="post"
          :aria-busy="pending"
          @submit.prevent="onSubmit"
        >
          <label class="label" for="text">{{ t('form.label') }}</label>
          <ClientOnly>
            <AdScreenshotOcr @append-text="onOcrAppend" />
          </ClientOnly>
          <textarea
            id="text"
            v-model="text"
            class="textarea"
            required
            rows="10"
            :placeholder="t('form.placeholder')"
            :aria-describedby="'wsc-form-text-hint wsc-form-ai-hint'"
            autocomplete="off"
          />
          <p id="wsc-form-text-hint" class="form__hint">{{ t('form.hint') }}</p>
          <div id="wsc-form-ai-hint" class="form__ai" role="group" aria-labelledby="wsc-ai-option-label">
            <div class="ai-toggle">
              <input
                id="wsc-ai-option"
                v-model="useAiEnabled"
                type="checkbox"
                class="ai-toggle__input"
                :disabled="pending"
              />
              <label for="wsc-ai-option" id="wsc-ai-option-label" class="ai-toggle__label">
                <span class="ai-toggle__track" aria-hidden="true">
                  <span class="ai-toggle__thumb" />
                </span>
                <span class="ai-toggle__text">{{ t('form.useAiLabel') }}</span>
              </label>
            </div>
            <p class="form__hint form__hint--ai">{{ t('form.useAiHint') }}</p>
          </div>
          <button
            type="button"
            class="btn"
            :class="{ 'btn--pending': pending }"
            :disabled="pending"
            :aria-busy="pending"
            @click="onSubmit"
          >
            <span class="btn__spinner" aria-hidden="true" />
            <span class="btn__label">{{ pending ? t('form.submitting') : t('form.submit') }}</span>
          </button>
          <Transition name="wsc-progress-panel">
            <div
              v-if="pending"
              ref="analyzeProgressEl"
              class="analyze-progress"
              role="status"
              aria-live="polite"
              aria-atomic="true"
            >
              <p class="analyze-progress__title">{{ t('form.progressTitle') }}</p>
              <ol class="analyze-progress__list">
                <li
                  v-for="(step, i) in analyzeStepsWithKind"
                  :key="i"
                  class="analyze-progress__item"
                  :class="`analyze-progress__item--${step.kind}`"
                >
                  <span class="analyze-progress__mark" aria-hidden="true">
                    <span v-if="step.kind === 'done'" class="analyze-progress__check">✓</span>
                    <span v-else-if="step.kind === 'active'" class="analyze-progress__pulse" />
                    <span v-else class="analyze-progress__dot" />
                  </span>
                  <span class="analyze-progress__text">{{ step.label }}</span>
                </li>
              </ol>
            </div>
          </Transition>
          <Transition name="wsc-alert">
            <p v-if="errorMsg" class="form__err" role="alert">{{ errorMsg }}</p>
          </Transition>
        </form>
      </div>

      <aside class="panel panel--how" aria-labelledby="how-it-works-title">
        <h2 id="how-it-works-title" class="section-title font-display section-title--first">
          {{ t('howItWorks.title') }}
        </h2>
        <p class="body-text how-lead">{{ t('howItWorks.lead') }}</p>
        <details class="how-details">
          <summary class="how-details__summary">{{ t('howItWorks.summaryToggle') }}</summary>
          <div class="how-details__body">
            <ol class="how-list">
              <li v-for="(line, i) in howSteps" :key="i" class="how-list__item">
                {{ line }}
              </li>
            </ol>
            <h3 class="how-subtitle font-display">{{ t('howItWorks.rulesTitle') }}</h3>
            <ul class="how-rules">
              <li class="how-rules__item">{{ t('howItWorks.rulePrice') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleMessaging') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleUrgency') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.rulePayment') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleTrust') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleIdentity') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleFees') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleForms') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleNarrative') }}</li>
              <li class="how-rules__item">{{ t('howItWorks.ruleTemplateEn') }}</li>
            </ul>
            <p class="body-text muted how-benchmark">
              <strong>{{ t('howItWorks.benchmarkTitle') }}</strong>
              {{ ' ' + t('howItWorks.benchmarkBody') }}
            </p>
            <p class="body-text muted how-disclaimer">{{ t('howItWorks.disclaimer') }}</p>
          </div>
        </details>
        <p class="body-text home-faq-teaser">
          <NuxtLink class="home-faq-teaser__link" :to="localePath('/faq')">{{ t('home.faqTeaser') }}</NuxtLink>
        </p>
      </aside>
      </div>

      <Transition name="wsc-result">
        <section
          v-if="result"
          ref="resultEl"
          class="panel panel--result"
          tabindex="-1"
          role="region"
          :aria-label="t('result.ariaRegion')"
          aria-live="polite"
          aria-relevant="additions text"
        >
          <div class="score-block" :class="`score-block--${scoreLevel(result.score)}`">
            <ScoreGauge
              :key="result.score"
              class="score-block__gauge"
              :score="result.score"
              :width="200"
              :aria-label="t('result.scoreGaugeAria', { n: result.score })"
            />
            <div class="score-block__meta">
              <span class="score-block__label">{{ t('result.scoreLabel') }}</span>
              <span class="score-block__risk">{{ riskLabel(result.score) }}</span>
              <span class="score-block__badges">
                <span class="pill pill--pop" :class="scorePillClass(result.score)">{{ t('result.scoreOutOf', { n: result.score }) }}</span>
                <span v-if="result.llm_used" class="meta-badge">{{ t('result.llmBadge') }}</span>
              </span>
            </div>
          </div>

          <div
            v-if="result.listing_fit"
            class="listing-fit"
            :class="`listing-fit--${result.listing_fit.tier}`"
            role="region"
            :aria-label="t('result.listingFitAria', { n: result.listing_fit.score })"
          >
            <h2 class="listing-fit__heading section-title font-display">{{ t('result.listingFitTitle') }}</h2>
            <div class="listing-fit__meter-wrap" aria-hidden="true">
              <div class="listing-fit__meter">
                <div
                  class="listing-fit__meter-fill"
                  :style="{ width: `${result.listing_fit.score}%` }"
                />
              </div>
              <span class="listing-fit__meter-label">{{ t('result.listingFitMatchLabel', { n: result.listing_fit.score }) }}</span>
            </div>
            <p class="listing-fit__headline">{{ listingFitHeadline }}</p>
            <p class="listing-fit__body">{{ listingFitBody }}</p>
          </div>

          <template v-if="result.listing_facts">
            <h2 class="section-title font-display">{{ t('result.listingFactsTitle') }}</h2>
            <div
              class="benchmark-focus"
              role="region"
              :aria-label="t('result.benchmarkAria')"
            >
              <p class="benchmark-focus__intro body-text">{{ benchmarkIntro }}</p>
              <p v-if="benchmarkVerdict" class="benchmark-focus__verdict body-text">{{ benchmarkVerdict }}</p>
            </div>
            <dl class="facts-grid">
              <dt class="facts-grid__dt">{{ t('result.listingFactsCity') }}</dt>
              <dd class="facts-grid__dd">{{ result.listing_facts.city ?? emDash }}</dd>
              <dt class="facts-grid__dt">{{ t('result.listingFactsStreet') }}</dt>
              <dd class="facts-grid__dd">{{ listingFactsStreetLine }}</dd>
              <dt class="facts-grid__dt">{{ t('result.listingFactsPrice') }}</dt>
              <dd class="facts-grid__dd">{{ formatFactsPrice(result.listing_facts.price_eur) }}</dd>
              <dt class="facts-grid__dt">{{ t('result.listingFactsBenchmark') }}</dt>
              <dd class="facts-grid__dd">{{ formatFactsPrice(result.listing_facts.benchmark_monthly_eur) }} / {{ t('result.listingFactsMonth') }}</dd>
              <dt class="facts-grid__dt">{{ t('result.listingFactsBenchmarkDiff') }}</dt>
              <dd class="facts-grid__dd">{{ listingFactsDiffLabel }}</dd>
              <template v-if="result.listing_facts.surface_m2 != null">
                <dt class="facts-grid__dt">{{ t('result.listingFactsSurfaceM2') }}</dt>
                <dd class="facts-grid__dd">{{ listingFactsSurfaceM2Label }}</dd>
                <template v-if="result.listing_facts.price_per_m2_month_eur != null">
                  <dt class="facts-grid__dt">{{ t('result.listingFactsPricePerM2') }}</dt>
                  <dd class="facts-grid__dd">
                    {{ formatFactsPrice(result.listing_facts.price_per_m2_month_eur) }} {{ t('result.listingFactsPerM2Suffix') }}
                  </dd>
                </template>
                <dt class="facts-grid__dt">{{ t('result.listingFactsBenchmarkPerM2') }}</dt>
                <dd class="facts-grid__dd">
                  {{ formatFactsPrice(result.listing_facts.benchmark_per_m2_month_eur) }} {{ t('result.listingFactsPerM2Suffix') }}
                  <span class="body-text muted"> · {{ t('result.listingFactsPerM2ModelNote', { m2: 55 }) }}</span>
                </dd>
                <template
                  v-if="
                    result.listing_facts.adjusted_benchmark_per_m2_month_eur != null
                      && result.listing_facts.price_per_m2_month_eur != null
                  "
                >
                  <dt class="facts-grid__dt">{{ t('result.listingFactsAdjustedPerM2') }}</dt>
                  <dd class="facts-grid__dd">
                    {{
                      new Intl.NumberFormat(locale.value === 'nl' ? 'nl-NL' : 'en-GB', {
                        style: 'currency',
                        currency: 'EUR',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                      }).format(result.listing_facts.adjusted_benchmark_per_m2_month_eur)
                    }}
                    {{ t('result.listingFactsPerM2Suffix') }}
                  </dd>
                  <dt class="facts-grid__dt">{{ t('result.listingFactsPerM2VsAdjusted') }}</dt>
                  <dd class="facts-grid__dd">{{ listingFactsPerM2DiffLabel }}</dd>
                </template>
                <template v-if="result.listing_facts.small_surface">
                  <dt class="facts-grid__dt">{{ t('result.listingFactsSmallSurfaceTitle') }}</dt>
                  <dd class="facts-grid__dd body-text muted">{{ t('result.listingFactsSmallSurfaceNote') }}</dd>
                </template>
              </template>
              <dt class="facts-grid__dt">{{ t('result.listingFactsSource') }}</dt>
              <dd class="facts-grid__dd">
                <a
                  v-if="result.listing_facts.source_url"
                  class="facts-grid__link"
                  :href="result.listing_facts.source_url"
                  target="_blank"
                  rel="noopener noreferrer"
                >{{ result.listing_facts.source_url }}</a>
                <span v-else>{{ emDash }}</span>
              </dd>
              <dt class="facts-grid__dt">{{ t('result.listingFactsContact') }}</dt>
              <dd class="facts-grid__dd">{{ result.listing_facts.contact_hint ?? emDash }}</dd>
              <template v-if="result.listing_facts.dwelling">
                <dt class="facts-grid__dt">{{ t('result.listingFactsDwellingKind') }}</dt>
                <dd class="facts-grid__dd">{{ dwellingKindLine }}</dd>
                <dt class="facts-grid__dt">{{ t('result.listingFactsDwellingSector') }}</dt>
                <dd class="facts-grid__dd">{{ dwellingSectorLine }}</dd>
                <template v-if="result.listing_facts.dwelling.signals?.length">
                  <dt class="facts-grid__dt">{{ t('result.listingFactsDwellingSignals') }}</dt>
                  <dd class="facts-grid__dd">{{ result.listing_facts.dwelling.signals.join(', ') }}</dd>
                </template>
              </template>
            </dl>
          </template>

          <p v-if="result.link_assessment" class="link-assessment body-text">{{ result.link_assessment }}</p>

          <p v-if="result.rule_score != null && result.rule_score !== result.score" class="rule-note body-text">
            {{ t('result.ruleEngine') }}: <strong>{{ result.rule_score }}</strong> — {{ t('result.finalScore') }}:
            <strong>{{ result.score }}</strong>.
          </p>

          <template v-if="result.methodology">
            <h2 class="section-title font-display">{{ t('result.methodology') }}</h2>
            <MethodologySteps :text="result.methodology" />
          </template>

          <template v-if="result.market_context">
            <h2 class="section-title font-display">{{ t('result.marketContext') }}</h2>
            <p class="body-text result-block">{{ result.market_context }}</p>
          </template>

          <template v-if="result.observations?.length">
            <h2 class="section-title font-display">{{ t('result.observationsTitle') }}</h2>
            <ul class="bullet-list">
              <li
                v-for="(line, i) in result.observations"
                :key="i"
                class="bullet-list__item"
              >
                {{ line }}
              </li>
            </ul>
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

          <template v-if="!result.listing_facts">
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
          </template>

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
            <ul class="verify-list">
              <li v-for="(line, i) in result.what_to_verify" :key="i" class="verify-list__item">
                <label class="verify-list__label">
                  <input type="checkbox" class="verify-list__box">
                  <span class="verify-list__text">{{ line }}</span>
                </label>
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

          <div v-if="result.report_pdf_url" class="share">
            <span class="share__label">{{ t('result.share') }}</span>
            <div class="share__actions">
              <a
                class="share__btn"
                :href="reportPdfHref"
                target="_blank"
                rel="noopener"
              >{{ t('result.downloadPdf') }}</a>
            </div>
          </div>
        </section>
      </Transition>
      </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { normalizeApiBase } from '~/utils/wscApiBase'

type RiskBreakdownRow = {
  category: string
  points: number
  detail?: string
}

type DwellingFacts = {
  kind: 'room' | 'whole' | 'unknown'
  kind_confidence: 'high' | 'medium' | 'low'
  rental_sector: 'private' | 'social' | 'unknown'
  sector_confidence: 'high' | 'medium' | 'low'
  signals: string[]
}

type ListingFacts = {
  city: string | null
  street: string | null
  house_number: string | null
  street_line: string | null
  price_eur: number | null
  source_url: string | null
  contact_hint: string | null
  benchmark_monthly_eur: number
  benchmark_diff_percent: number | null
  benchmark_city: string | null
  benchmark_scope: 'national' | 'municipality'
  dwelling?: DwellingFacts
  surface_m2?: number | null
  price_per_m2_month_eur?: number | null
  benchmark_per_m2_month_eur?: number
  adjusted_benchmark_per_m2_month_eur?: number | null
  per_m2_vs_adjusted_percent?: number | null
  small_surface?: boolean
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
  observations?: string[]
  id?: number
  report_pdf_url?: string
  report_slug?: string
  listing_facts?: ListingFacts
  listing_fit?: {
    tier: 'strong' | 'mixed' | 'weak'
    score: number
    reason_codes: string[]
  }
}

const { t, locale } = useI18n()
const localePath = useLocalePath()
const { themeDisplay } = useWscThemeDisplay()
const config = useRuntimeConfig()
const route = useRoute()

const emDash = '—'

function formatFactsPrice(n: number | null | undefined): string {
  if (n == null)
    return emDash
  return new Intl.NumberFormat(locale.value === 'nl' ? 'nl-NL' : 'en-GB', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
  }).format(n)
}

const listingFactsStreetLine = computed(() => {
  const f = result.value?.listing_facts
  if (!f)
    return emDash
  if (f.street_line)
    return f.street_line
  if (f.street)
    return f.street
  return emDash
})

const listingFactsDiffLabel = computed(() => {
  const f = result.value?.listing_facts
  if (!f || f.benchmark_diff_percent == null)
    return emDash
  return `${f.benchmark_diff_percent > 0 ? '+' : ''}${f.benchmark_diff_percent}%`
})

function dwellingConfLabel(c: string): string {
  if (c === 'high')
    return t('result.dwellingConfHigh')
  if (c === 'medium')
    return t('result.dwellingConfMedium')
  return t('result.dwellingConfLow')
}

const dwellingKindLine = computed(() => {
  const d = result.value?.listing_facts?.dwelling
  if (!d)
    return emDash
  const label = d.kind === 'room'
    ? t('result.dwellingKindRoom')
    : d.kind === 'whole'
      ? t('result.dwellingKindWhole')
      : t('result.dwellingKindUnknown')
  return `${label} · ${dwellingConfLabel(d.kind_confidence)}`
})

const dwellingSectorLine = computed(() => {
  const d = result.value?.listing_facts?.dwelling
  if (!d)
    return emDash
  const label = d.rental_sector === 'private'
    ? t('result.dwellingSectorPrivate')
    : d.rental_sector === 'social'
      ? t('result.dwellingSectorSocial')
      : t('result.dwellingSectorUnknown')
  return `${label} · ${dwellingConfLabel(d.sector_confidence)}`
})

const listingFactsSurfaceM2Label = computed(() => {
  const s = result.value?.listing_facts?.surface_m2
  if (s == null || Number.isNaN(Number(s)))
    return emDash
  const n = Number(s)
  const maxFrac = Number.isInteger(n) ? 0 : 1
  return `${new Intl.NumberFormat(locale.value === 'nl' ? 'nl-NL' : 'en-GB', {
    minimumFractionDigits: maxFrac,
    maximumFractionDigits: maxFrac,
  }).format(n)} m²`
})

const listingFactsPerM2DiffLabel = computed(() => {
  const p = result.value?.listing_facts?.per_m2_vs_adjusted_percent
  if (p == null)
    return emDash
  return `${p > 0 ? '+' : ''}${p}%`
})

const benchmarkIntro = computed(() => {
  const f = result.value?.listing_facts
  if (!f)
    return ''
  const avg = formatFactsPrice(f.benchmark_monthly_eur)
  if (f.benchmark_scope === 'national')
    return t('result.benchmarkIntroNational', { avg })
  const city = (f.benchmark_city ?? f.city ?? '').trim()
  if (!city)
    return t('result.benchmarkIntroNational', { avg })

  return t('result.benchmarkIntroMunicipality', { city, avg })
})

const benchmarkVerdict = computed(() => {
  const f = result.value?.listing_facts
  if (!f)
    return ''
  if (f.price_eur == null)
    return t('result.benchmarkVerdictNoPrice')
  const d = f.benchmark_diff_percent
  if (d == null)
    return ''
  if (d <= -30)
    return t('result.benchmarkVerdictFarBelow', { pct: d })
  if (d < -10)
    return t('result.benchmarkVerdictBelow', { pct: d })
  if (d <= 10)
    return t('result.benchmarkVerdictAround', { pct: d })

  return t('result.benchmarkVerdictAbove', { pct: d })
})

const howSteps = computed(() => [
  t('howItWorks.step1'),
  t('howItWorks.step2'),
  t('howItWorks.step3'),
  t('howItWorks.step4'),
  t('howItWorks.step5'),
])

const USE_AI_STORAGE = 'wsc-use-ai'

const text = ref('')
const useAiEnabled = ref(false)
const pending = ref(false)
const errorMsg = ref('')
const result = ref<AnalyzeResponse | null>(null)
const resultEl = ref<HTMLElement | null>(null)
const analyzeProgressEl = ref<HTMLElement | null>(null)
const analyzeCurrentStep = ref(0)
let analyzeStepTimer: ReturnType<typeof setInterval> | null = null

const analyzeStepLabels = computed(() => {
  const steps = [
    t('form.progressStepParse'),
    t('form.progressStepMarket'),
    t('form.progressStepRules'),
  ]
  if (useAiEnabled.value)
    steps.push(t('form.progressStepAi'))
  steps.push(t('form.progressStepReport'))
  return steps
})

const analyzeStepsWithKind = computed(() => {
  const labels = analyzeStepLabels.value
  const cur = analyzeCurrentStep.value
  return labels.map((label, i) => ({
    label,
    kind: (i < cur ? 'done' : i === cur ? 'active' : 'upcoming') as 'done' | 'active' | 'upcoming',
  }))
})

function clearAnalyzeStepTimer() {
  if (analyzeStepTimer != null) {
    clearInterval(analyzeStepTimer)
    analyzeStepTimer = null
  }
}

watch(pending, async (isPending) => {
  clearAnalyzeStepTimer()
  if (!isPending) {
    analyzeCurrentStep.value = 0
    return
  }
  analyzeCurrentStep.value = 0
  const total = analyzeStepLabels.value.length
  analyzeStepTimer = setInterval(() => {
    if (analyzeCurrentStep.value < total - 1)
      analyzeCurrentStep.value++
  }, 650)
  await nextTick()
  if (!import.meta.client)
    return
  const el = analyzeProgressEl.value
  if (!el)
    return
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  el.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'nearest' })
})

onUnmounted(() => {
  clearAnalyzeStepTimer()
})

onMounted(() => {
  if (!import.meta.client)
    return
  useAiEnabled.value = localStorage.getItem(USE_AI_STORAGE) === '1'
  // Startpagina en de zoekactie (JSON-LD SearchAction) sturen tekst mee via ?text=
  const q = route.query.text
  const prefill = (Array.isArray(q) ? q[0] : q)?.toString().trim()
  if (prefill) {
    text.value = prefill.slice(0, 20000)
    if (route.query.run === '1')
      void onSubmit()
  }
})

watch(useAiEnabled, (v) => {
  if (!import.meta.client)
    return
  localStorage.setItem(USE_AI_STORAGE, v ? '1' : '0')
})

/** PDF: match app theme (resolved light/dark) and UI language (nl/en). */
const listingFitHeadline = computed(() => {
  const tier = result.value?.listing_fit?.tier
  if (tier === 'strong')
    return t('result.listingFitStrong')
  if (tier === 'mixed')
    return t('result.listingFitMixed')
  if (tier === 'weak')
    return t('result.listingFitWeak')
  return ''
})

const listingFitBody = computed(() => {
  const tier = result.value?.listing_fit?.tier
  if (tier === 'strong')
    return t('result.listingFitStrongBody')
  if (tier === 'mixed')
    return t('result.listingFitMixedBody')
  if (tier === 'weak')
    return t('result.listingFitWeakBody')
  return ''
})

const reportPdfHref = computed(() => {
  const raw = result.value?.report_pdf_url
  if (!raw)
    return '#'
  try {
    const u = new URL(raw, typeof window !== 'undefined' ? window.location.origin : 'http://localhost')
    u.searchParams.set('theme', themeDisplay.value === 'dark' ? 'dark' : 'light')
    u.searchParams.set('locale', locale.value === 'en' ? 'en' : 'nl')
    return u.toString()
  }
  catch {
    return raw
  }
})

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

useWscSeo({
  title: () => t('check.metaTitle'),
  description: () => t('check.metaDescription'),
  path: '/check',
})

function apiUrl(path: string): string {
  const p = path.startsWith('/') ? path : `/${path}`
  const base = normalizeApiBase(String(config.public.apiBase ?? '').trim(), {
    allowHttp: config.public.apiAllowHttp === true,
  })
  // Leeg = zelfde origin (productie: Nuxt + Laravel op dehuurradar.nl)
  if (!base)
    return p
  return `${base.replace(/\/$/, '')}${p}`
}

function scoreLevel(n: number): 'low' | 'mid' | 'high' {
  if (n <= 30) return 'low'
  if (n <= 60) return 'mid'
  return 'high'
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

function onOcrAppend(chunk: string) {
  const cur = text.value.trim()
  text.value = cur ? `${cur}\n\n${chunk}` : chunk
}

async function onSubmit(e?: Event) {
  e?.preventDefault()
  errorMsg.value = ''
  result.value = null
  pending.value = true
  try {
    const data = await $fetch<AnalyzeResponse>(apiUrl('/api/analyze'), {
      method: 'POST',
      body: {
        text: text.value,
        use_ai: useAiEnabled.value,
        locale: locale.value === 'en' ? 'en' : 'nl',
      },
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
.main--check {
  padding-bottom: 4rem;
}

.check-nav {
  margin: 0 0 0.5rem;
}

.check-body {
  margin-top: clamp(1.5rem, 4vw, 2.5rem);
}

.check-layout {
  display: grid;
  gap: 1.25rem;
  align-items: start;
}

@media (min-width: 960px) {
  .check-layout {
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
  }
  .panel--how {
    position: sticky;
    top: 5.5rem;
  }
}

/* Kaarten: vlak, mistgroen, geen schaduw */
.panel {
  background: var(--surface-elevated);
  border-radius: var(--radius-lg);
  padding: clamp(1.25rem, 3vw, 2rem);
}

.panel--form {
  grid-row: 1;
}

.panel--result {
  margin-top: 1.5rem;
  background: var(--surface-page);
  border: 1.5px solid var(--border-subtle);
  outline: none;
}

/* Krijgt programmatisch focus voor schermlezers; geen zichtbare ring nodig */
.panel--result:focus {
  outline: none;
}

.section-title {
  margin: 2rem 0 0.75rem;
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  line-height: 1.25;
  color: var(--text-primary);
}

.section-title--first {
  margin-top: 0;
}

.body-text {
  margin: 0 0 0.75rem;
  line-height: 1.6;
  color: var(--text-primary);
}

.muted {
  color: var(--text-secondary);
}

/* Hoe werkt het */
.how-lead {
  color: var(--text-secondary);
}

.how-details {
  margin: 0.5rem 0 1rem;
  border-radius: var(--radius-md);
  background: var(--surface-page);
}

.how-details__summary {
  list-style: none;
  cursor: pointer;
  padding: 0.85rem 1rem;
  font-weight: 700;
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  border-radius: var(--radius-md);
}

.how-details__summary::-webkit-details-marker {
  display: none;
}

.how-details__summary::after {
  content: '+';
  font-size: 1.3rem;
  line-height: 1;
}

.how-details[open] .how-details__summary::after {
  content: '−';
}

.how-details__summary:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

.how-details__body {
  padding: 0 1rem 1rem;
}

.how-list {
  margin: 0 0 1rem;
  padding: 0;
  list-style: none;
  counter-reset: how;
  display: grid;
  gap: 0.6rem;
}

.how-list__item {
  counter-increment: how;
  display: grid;
  grid-template-columns: 1.75rem 1fr;
  gap: 0.6rem;
  line-height: 1.5;
  color: var(--text-primary);
}

.how-list__item::before {
  content: counter(how);
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand);
  font-weight: 800;
  font-size: 0.85rem;
  display: grid;
  place-items: center;
}

.how-subtitle {
  margin: 1.25rem 0 0.5rem;
  font-size: 1rem;
  font-weight: 800;
}

.how-rules {
  margin: 0 0 1rem;
  padding-left: 1.1rem;
  display: grid;
  gap: 0.35rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.how-rules__item::marker {
  color: var(--action);
}

.how-benchmark,
.how-disclaimer {
  font-size: 0.92rem;
}

.home-faq-teaser {
  margin: 0;
}

.home-faq-teaser__link {
  font-weight: 700;
}

/* Formulier */
.form {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.label {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.textarea {
  width: 100%;
  min-height: 14rem;
  padding: 1rem 1.1rem;
  border: 1.5px solid var(--border-subtle);
  border-radius: var(--radius-md);
  background: var(--surface-input);
  color: var(--text-primary);
  font: inherit;
  font-size: 1rem;
  line-height: 1.55;
  resize: vertical;
  transition: border-color var(--duration-fast) var(--ease-out);
}

.textarea::placeholder {
  color: var(--text-tertiary);
}

.textarea:hover {
  border-color: var(--border-strong);
}

.textarea:focus-visible {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-muted);
}

.form__hint {
  margin: -0.25rem 0 0;
  font-size: 0.9rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.form__ai {
  display: grid;
  gap: 0.4rem;
  padding: 0.9rem 1rem;
  border-radius: var(--radius-md);
  background: var(--surface-page);
}

.form__hint--ai {
  margin: 0;
}

.ai-toggle {
  position: relative;
}

.ai-toggle__input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

.ai-toggle__label {
  display: inline-flex;
  align-items: center;
  gap: 0.7rem;
  cursor: pointer;
  font-weight: 700;
  color: var(--text-primary);
}

.ai-toggle__track {
  position: relative;
  flex: 0 0 auto;
  width: 46px;
  height: 28px;
  border-radius: 999px;
  background: var(--border-strong);
  transition: background var(--duration-fast) var(--ease-out);
}

.ai-toggle__thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #fff;
  transition: transform var(--duration-fast) var(--ease-out);
}

.ai-toggle__input:checked + .ai-toggle__label .ai-toggle__track {
  background: var(--accent);
}

html.dark .ai-toggle__input:checked + .ai-toggle__label .ai-toggle__thumb {
  background: var(--brand);
}

.ai-toggle__input:checked + .ai-toggle__label .ai-toggle__thumb {
  transform: translateX(18px);
}

.ai-toggle__input:focus-visible + .ai-toggle__label .ai-toggle__track {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

.ai-toggle__input:disabled + .ai-toggle__label {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Hoofdknop: koraal */
.btn {
  align-self: flex-start;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  min-height: 56px;
  padding: 0 1.75rem;
  border: 0;
  border-radius: var(--radius-md);
  background: var(--action);
  color: var(--on-action);
  font: inherit;
  font-size: 1.05rem;
  font-weight: 800;
  cursor: pointer;
  touch-action: manipulation;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.btn:hover:not(:disabled) {
  background: var(--action-hover);
}

.btn:active:not(:disabled) {
  transform: scale(0.98);
}

.btn:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

.btn:disabled {
  cursor: progress;
  opacity: 0.85;
}

.btn__spinner {
  display: none;
  width: 1.1rem;
  height: 1.1rem;
  border-radius: 50%;
  border: 2.5px solid currentColor;
  border-right-color: transparent;
}

.btn--pending .btn__spinner {
  display: inline-block;
  animation: wsc-analyze-spin 0.8s linear infinite;
}

@media (max-width: 520px) {
  .btn {
    align-self: stretch;
  }
}

@media (prefers-reduced-motion: reduce) {
  .btn:active:not(:disabled) {
    transform: none;
  }
  .btn--pending .btn__spinner {
    animation-duration: 2.4s;
  }
}

@keyframes wsc-analyze-spin {
  to {
    transform: rotate(360deg);
  }
}

/* Voortgang: groene kaart */
.analyze-progress {
  padding: 1.1rem 1.25rem;
  border-radius: var(--radius-md);
  background: var(--brand);
  color: var(--on-brand);
}

.analyze-progress__title {
  margin: 0 0 0.7rem;
  font-weight: 800;
}

.analyze-progress__list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.5rem;
}

.analyze-progress__item {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  color: var(--on-brand-muted);
}

.analyze-progress__item--active {
  color: var(--on-brand);
  font-weight: 700;
}

.analyze-progress__item--upcoming {
  opacity: 0.6;
}

.analyze-progress__mark {
  width: 1.4rem;
  height: 1.4rem;
  display: grid;
  place-items: center;
  flex: 0 0 auto;
}

.analyze-progress__check {
  width: 1.4rem;
  height: 1.4rem;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand);
  font-size: 0.8rem;
  font-weight: 800;
  display: grid;
  place-items: center;
}

.analyze-progress__pulse {
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  background: var(--action);
}

.analyze-progress__dot {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
  background: var(--on-brand-muted);
}

@media (prefers-reduced-motion: no-preference) {
  .analyze-progress__pulse {
    animation: wsc-progress-pulse 1.1s ease-in-out infinite;
  }
}

@keyframes wsc-progress-pulse {
  50% {
    transform: scale(1.35);
    opacity: 0.7;
  }
}

.form__err {
  margin: 0;
  padding: 0.85rem 1rem;
  border-radius: var(--radius-md);
  background: var(--err-bg);
  color: var(--err);
  font-weight: 600;
}

/* Uitslag: donkergroene scorekaart */
.score-block {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem 2rem;
  padding: clamp(1.25rem, 3vw, 2rem);
  margin: calc(-1 * clamp(1.25rem, 3vw, 2rem)) calc(-1 * clamp(1.25rem, 3vw, 2rem)) 1.5rem;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
  background: var(--brand);
  color: var(--on-brand);
}

.score-block__gauge {
  flex: 0 0 auto;
}

.score-block__meta {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-width: 0;
}

.score-block__label {
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--on-brand-muted);
}

.score-block__risk {
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.035em;
  line-height: 1.05;
}

.score-block--low .score-block__risk {
  color: #8fe3b8;
}

.score-block--mid .score-block__risk {
  color: #f2c14e;
}

.score-block--high .score-block__risk {
  color: #ff9c85;
}

.score-block__badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  margin-top: 0.3rem;
}

.pill {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  padding: 0 0.8rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
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

.score-block .pill--low { background: #ddf3e6; color: #1e6b44; }
.score-block .pill--mid { background: #fdf0d5; color: #7a4f00; }
.score-block .pill--high { background: #fce3dc; color: #a12e18; }

.meta-badge {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  padding: 0 0.8rem;
  border-radius: 999px;
  background: var(--on-brand-line);
  color: var(--brand-soft);
  font-size: 0.85rem;
  font-weight: 700;
}

/* Hoe goed past de advertentie */
.listing-fit {
  padding: 1.1rem 1.25rem;
  border-radius: var(--radius-md);
  background: var(--surface-elevated);
  margin-bottom: 1rem;
}

.listing-fit__heading {
  margin-top: 0;
}

.listing-fit__meter-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.6rem;
}

.listing-fit__meter {
  flex: 1;
  height: 10px;
  border-radius: 999px;
  background: var(--meter-track);
  overflow: hidden;
}

.listing-fit__meter-fill {
  height: 100%;
  border-radius: inherit;
  background: var(--risk-low-meter);
}

.listing-fit--mixed .listing-fit__meter-fill {
  background: var(--risk-mid-meter);
}

.listing-fit--weak .listing-fit__meter-fill {
  background: var(--risk-high-meter);
}

.listing-fit__meter-label {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--text-secondary);
  white-space: nowrap;
}

.listing-fit__headline {
  margin: 0 0 0.3rem;
  font-weight: 800;
  color: var(--text-primary);
}

.listing-fit__body {
  margin: 0;
  line-height: 1.55;
  color: var(--text-secondary);
}

/* Marktvergelijking */
.benchmark-focus {
  margin: 0 0 1rem;
  padding: 1rem 1.15rem;
  border-radius: var(--radius-md);
  background: var(--brand-soft);
  color: var(--brand);
}

html.dark .benchmark-focus {
  background: var(--accent-muted);
  color: var(--text-primary);
}

.benchmark-focus .body-text {
  color: inherit;
}

.benchmark-focus__verdict {
  margin-bottom: 0;
  font-weight: 700;
}

.facts-grid {
  display: grid;
  grid-template-columns: minmax(9rem, 14rem) 1fr;
  margin: 0;
  border-radius: var(--radius-md);
  background: var(--surface-elevated);
  overflow: hidden;
}

.facts-grid__dt,
.facts-grid__dd {
  margin: 0;
  padding: 0.7rem 1rem;
  border-bottom: 1px solid var(--border-subtle);
  line-height: 1.45;
}

.facts-grid__dt {
  font-weight: 600;
  color: var(--text-secondary);
}

.facts-grid__dd {
  color: var(--text-primary);
  font-weight: 600;
  overflow-wrap: anywhere;
}

.facts-grid__dt:nth-last-of-type(1),
.facts-grid__dd:nth-last-of-type(1) {
  border-bottom: 0;
}

.facts-grid__link {
  word-break: break-all;
}

@media (max-width: 520px) {
  .facts-grid {
    grid-template-columns: 1fr;
  }
  .facts-grid__dt {
    border-bottom: 0;
    padding-bottom: 0;
    font-size: 0.85rem;
  }
}

.link-assessment,
.rule-note {
  padding: 0.85rem 1rem;
  border-radius: var(--radius-md);
  background: var(--surface-elevated);
}

.result-block {
  max-width: 70ch;
}

/* Risico-opbouw met puntenbadges */
.risk-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.6rem;
}

.risk-list__item {
  display: grid;
  gap: 0.3rem;
  padding: 0.9rem 1rem;
  border-radius: var(--radius-md);
  background: var(--surface-elevated);
}

.risk-list__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.risk-list__cat {
  font-weight: 700;
  color: var(--text-primary);
}

.risk-list__pts {
  flex: 0 0 auto;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: var(--risk-high-bg);
  color: var(--risk-high);
  font-size: 0.85rem;
  font-weight: 800;
  white-space: nowrap;
}

.risk-list__detail {
  font-size: 0.93rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

/* Signalen */
.flag-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.5rem;
}

.flag-list__item {
  position: relative;
  padding: 0.75rem 1rem 0.75rem 2.4rem;
  border-radius: var(--radius-md);
  background: var(--risk-high-bg);
  color: var(--text-primary);
  line-height: 1.5;
}

.flag-list__item::before {
  content: '';
  position: absolute;
  left: 1rem;
  top: 1.15rem;
  width: 0.6rem;
  height: 0.6rem;
  border-radius: 50%;
  background: var(--action);
}

@media (prefers-reduced-motion: no-preference) {
  .flag-list__item {
    animation: wsc-flag-in var(--duration-normal) var(--ease-out) both;
    animation-delay: calc(var(--i, 0) * 45ms);
  }
}

@keyframes wsc-flag-in {
  from {
    opacity: 0;
    transform: translateY(0.3rem);
  }
}

.bullet-list {
  margin: 0;
  padding-left: 1.2rem;
  display: grid;
  gap: 0.45rem;
  line-height: 1.55;
  color: var(--text-primary);
}

.bullet-list__item::marker {
  color: var(--accent);
}

/* Controlelijst met vinkjes */
.verify-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.5rem;
}

.verify-list__label {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.8rem 1rem;
  border-radius: var(--radius-md);
  background: var(--surface-elevated);
  cursor: pointer;
  line-height: 1.5;
  color: var(--text-primary);
}

.verify-list__box {
  flex: 0 0 auto;
  width: 1.3rem;
  height: 1.3rem;
  margin: 0.1rem 0 0;
  accent-color: var(--accent);
  cursor: pointer;
}

.verify-list__box:checked + .verify-list__text {
  color: var(--text-secondary);
  text-decoration: line-through;
}

.summary-lead {
  font-size: 1.1rem;
  font-weight: 700;
}

.narrative,
.summary {
  max-width: 70ch;
}

.summary {
  white-space: pre-line;
}

/* Delen / PDF */
.share {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem 1rem;
  margin-top: 2rem;
  padding: 1rem 1.25rem;
  border-radius: var(--radius-md);
  background: var(--brand-soft);
  color: var(--brand);
}

html.dark .share {
  background: var(--accent-muted);
  color: var(--text-primary);
}

.share__label {
  font-weight: 800;
}

.share__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.share__btn {
  display: inline-flex;
  align-items: center;
  min-height: 48px;
  padding: 0 1.25rem;
  border-radius: var(--radius-md);
  background: var(--brand);
  color: var(--on-brand);
  font-weight: 700;
  text-decoration: none;
}

html.dark .share__btn {
  background: var(--brand-soft);
  color: var(--brand);
}

.share__btn:hover {
  background: var(--accent-hover);
  color: var(--on-brand);
  text-decoration: none;
}

html.dark .share__btn:hover {
  color: var(--brand);
}

/* Overgangen */
.wsc-progress-panel-enter-active,
.wsc-progress-panel-leave-active {
  transition:
    opacity var(--duration-normal) var(--ease-out),
    transform var(--duration-normal) var(--ease-out);
}

.wsc-progress-panel-enter-from,
.wsc-progress-panel-leave-to {
  opacity: 0;
  transform: translateY(0.4rem);
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
  .wsc-progress-panel-enter-from,
  .wsc-progress-panel-leave-to,
  .wsc-result-enter-from,
  .wsc-result-leave-to,
  .wsc-alert-enter-from,
  .wsc-alert-leave-to {
    transform: none;
  }
}
</style>
