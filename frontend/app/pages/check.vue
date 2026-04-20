<template>
  <div>
    <Transition name="wsc-locale" mode="out-in">
      <div :key="locale" class="main main--check">
      <Transition name="wsc-nav-back" appear>
        <p class="check-nav">
          <NuxtLink class="check-nav__link wsc-back-link" :to="localePath('/')">
            <span class="wsc-back-link__arrow" aria-hidden="true">←</span>
            <span class="check-nav__text">{{ t('check.backHome') }}</span>
          </NuxtLink>
        </p>
      </Transition>

      <header class="check-head" aria-labelledby="check-page-title">
        <h1 id="check-page-title" class="check-head__title font-display">{{ t('check.pageTitle') }}</h1>
        <p class="check-head__lead">{{ t('check.pageLead') }}</p>
      </header>

      <section class="panel panel--how" aria-labelledby="how-it-works-title">
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
      </section>

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
          <div class="score-block">
            <div class="score-block__num" :key="result.score">{{ result.score }}</div>
            <div class="score-block__meta">
              <span class="score-block__label">{{ t('result.scoreLabel') }}</span>
              <span class="pill pill--pop" :class="scorePillClass(result.score)">{{ riskLabel(result.score) }}</span>
              <span v-if="result.llm_used" class="meta-badge">{{ t('result.llmBadge') }}</span>
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
            <p class="body-text muted result-block methodology-text">{{ result.methodology }}</p>
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
      body: { text: text.value, use_ai: useAiEnabled.value },
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

.check-nav {
  margin: 0;
}

.check-head {
  margin: 0;
  padding-bottom: 0.15rem;
}

@media (prefers-reduced-motion: no-preference) {
  .check-head {
    animation: wsc-fade-in-up 0.52s var(--ease-out) 0.04s backwards;
  }
}

@media (prefers-reduced-motion: reduce) {
  .check-head {
    animation: none;
  }
}

.check-head__title {
  font-size: clamp(1.35rem, 3.5vw, 1.75rem);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.2;
  margin: 0 0 0.45rem;
  color: var(--text-primary);
}

.check-head__lead {
  margin: 0;
  font-size: 0.98rem;
  color: var(--text-secondary);
  line-height: 1.55;
  max-width: 40rem;
}

.main {
  flex: 1;
  max-width: 960px;
  margin: 0 auto;
  width: 100%;
  min-width: 0;
  padding:
    max(1.25rem, env(safe-area-inset-top, 0px))
    max(1.25rem, env(safe-area-inset-right, 0px))
    max(3.25rem, env(safe-area-inset-bottom, 0px))
    max(1.25rem, env(safe-area-inset-left, 0px));
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
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
  .main > .panel--how {
    animation: wsc-panel-rise 0.58s var(--ease-out) backwards;
  }

  .main > .panel--form {
    animation: wsc-panel-rise 0.58s var(--ease-out) 0.08s backwards;
  }

  .main > .panel--result {
    animation: wsc-panel-rise 0.52s var(--ease-out) backwards;
  }
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .main > .panel--how:hover,
  .main > .panel--form:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-panel-hover);
    border-color: color-mix(in srgb, var(--accent) 12%, var(--border-subtle));
  }
}

.panel--result {
  border-color: color-mix(in srgb, var(--accent) 25%, var(--border-subtle));
}

.panel--result:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
}

.panel--how {
  border-color: color-mix(in srgb, var(--accent) 18%, var(--border-subtle));
}

.how-lead {
  margin: 0 0 0.85rem;
  color: var(--text-secondary);
  line-height: 1.65;
}

.section-title--first {
  margin-top: 0;
}

.how-details {
  margin-top: 0.15rem;
}

.how-details__summary {
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--accent);
  list-style: none;
  min-height: 44px;
  display: flex;
  align-items: center;
  touch-action: manipulation;
  transition: color var(--duration-fast) var(--ease-out);
}

.how-details[open] .how-details__summary {
  color: var(--accent-hover);
}

.how-details__summary::-webkit-details-marker {
  display: none;
}

.how-details__summary::marker {
  content: '';
}

.how-details__body {
  margin-top: 0.85rem;
  padding-top: 0.85rem;
  border-top: 1px solid var(--border-subtle);
}

@media (prefers-reduced-motion: no-preference) {
  .how-details[open] .how-details__body {
    animation: wsc-fade-in 0.38s var(--ease-out) both;
  }
}

.how-list {
  margin: 0 0 1rem;
  padding-left: 1.25rem;
  color: var(--text-secondary);
  line-height: 1.65;
}

.how-list__item {
  margin: 0.45rem 0;
}

.how-subtitle {
  font-size: 0.92rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  color: var(--text-primary);
}

.how-rules {
  margin: 0 0 1rem;
  padding-left: 1.25rem;
  color: var(--text-secondary);
  line-height: 1.55;
}

.how-rules__item {
  margin: 0.4rem 0;
}

.how-benchmark,
.how-disclaimer {
  margin: 0.65rem 0 0;
  font-size: 0.88rem;
}

.home-faq-teaser {
  margin: 1.1rem 0 0;
  font-size: 0.92rem;
  line-height: 1.5;
}

.home-faq-teaser__link {
  color: var(--accent);
  font-weight: 600;
  text-decoration: underline;
  text-decoration-color: color-mix(in srgb, var(--accent) 38%, transparent);
  text-decoration-thickness: 1.5px;
  text-underline-offset: 0.18em;
  transition:
    color var(--duration-fast) var(--ease-out),
    text-decoration-color var(--duration-fast) var(--ease-out),
    text-decoration-thickness var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.home-faq-teaser__link:hover {
  color: var(--accent-hover);
  text-decoration-color: color-mix(in srgb, var(--accent-hover) 72%, transparent);
  text-decoration-thickness: 2px;
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .home-faq-teaser__link:hover {
    transform: translateY(-1px);
  }
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
  box-shadow:
    0 0 0 3px var(--accent-muted),
    0 4px 18px color-mix(in srgb, var(--accent) 12%, transparent);
}

@media (max-width: 520px) {
  .textarea {
    font-size: 16px;
  }
}

.form__hint {
  font-size: 0.8rem;
  color: var(--text-tertiary);
  margin: 0;
}

.form__ai {
  margin: 0.35rem 0 0.25rem;
  padding: 0.75rem 0.85rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: color-mix(in srgb, var(--surface-page) 92%, var(--accent) 8%);
}

.form__hint--ai {
  margin-top: 0.45rem;
  line-height: 1.45;
}

.ai-toggle {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.ai-toggle__input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.ai-toggle__label {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-primary);
  user-select: none;
}

.ai-toggle__input:disabled + .ai-toggle__label {
  opacity: 0.6;
  cursor: not-allowed;
}

.ai-toggle__track {
  position: relative;
  flex-shrink: 0;
  width: 2.5rem;
  height: 1.35rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--border-strong) 75%, var(--surface-page));
  transition:
    background var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

.ai-toggle__thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: calc(1.35rem - 4px);
  height: calc(1.35rem - 4px);
  border-radius: 50%;
  background: var(--surface-page);
  box-shadow: var(--shadow-sm);
  transition:
    transform 0.22s var(--ease-spring),
    background var(--duration-fast) var(--ease-out);
}

.ai-toggle__input:focus-visible + .ai-toggle__label .ai-toggle__track {
  box-shadow: 0 0 0 3px var(--accent-muted);
}

.ai-toggle__input:checked + .ai-toggle__label .ai-toggle__track {
  background: color-mix(in srgb, var(--accent) 88%, var(--border-strong));
}

.ai-toggle__input:checked + .ai-toggle__label .ai-toggle__thumb {
  transform: translateX(1.15rem);
}

@media (prefers-reduced-motion: reduce) {
  .ai-toggle__thumb {
    transition: none;
  }
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
  touch-action: manipulation;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    opacity var(--duration-fast) var(--ease-out);
}

@media (max-width: 520px) {
  .btn {
    align-self: stretch;
    width: 100%;
    min-height: 48px;
  }
}

@media (hover: hover) and (pointer: fine) {
  .btn:hover:not(:disabled) {
    background: var(--accent-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }
}

.btn:active:not(:disabled) {
  transform: translateY(0) scale(0.99);
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn:focus-visible {
  outline: 2px solid var(--surface-elevated);
  outline-offset: 2px;
  box-shadow:
    0 0 0 4px var(--accent-muted),
    0 4px 18px color-mix(in srgb, var(--accent) 18%, transparent);
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

.analyze-progress {
  margin-top: 1rem;
  padding: 1rem 1rem 1.05rem;
  border-radius: var(--radius-md);
  border: 1px solid color-mix(in srgb, var(--accent) 28%, var(--border-subtle));
  background: color-mix(in srgb, var(--surface-page) 94%, var(--accent) 6%);
  box-shadow: 0 8px 28px color-mix(in srgb, var(--accent) 8%, transparent);
}

.analyze-progress__title {
  margin: 0 0 0.75rem;
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--accent);
}

.analyze-progress__list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.analyze-progress__item {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--text-secondary);
  transition: color var(--duration-fast) var(--ease-out);
}

.analyze-progress__item--done {
  color: var(--text-primary);
}

.analyze-progress__item--done .analyze-progress__text {
  opacity: 0.92;
}

.analyze-progress__item--active {
  color: var(--text-primary);
  font-weight: 600;
}

.analyze-progress__item--active .analyze-progress__text {
  color: var(--text-primary);
}

.analyze-progress__item--upcoming {
  opacity: 0.72;
}

.analyze-progress__mark {
  flex-shrink: 0;
  width: 1.35rem;
  height: 1.35rem;
  margin-top: 0.08rem;
  display: grid;
  place-items: center;
}

.analyze-progress__check {
  width: 1.2rem;
  height: 1.2rem;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 0.65rem;
  font-weight: 800;
  color: #fff;
  background: color-mix(in srgb, var(--accent) 92%, #000);
  line-height: 1;
}

.analyze-progress__dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 50%;
  background: var(--text-tertiary);
  opacity: 0.45;
}

.analyze-progress__pulse {
  width: 0.85rem;
  height: 0.85rem;
  border-radius: 50%;
  border: 2px solid color-mix(in srgb, var(--accent) 55%, var(--border-strong));
  border-top-color: var(--accent);
  animation: wsc-analyze-spin 0.75s linear infinite;
}

@media (prefers-reduced-motion: reduce) {
  .analyze-progress__pulse {
    animation: none;
    border-color: var(--accent);
    opacity: 0.9;
  }
}

@keyframes wsc-analyze-spin {
  to {
    transform: rotate(360deg);
  }
}

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

@media (prefers-reduced-motion: reduce) {
  .wsc-progress-panel-enter-from,
  .wsc-progress-panel-leave-to {
    transform: none;
  }
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

.benchmark-focus {
  margin: 0 0 0.85rem;
  padding: 0.85rem 1rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  border-left: 3px solid var(--accent);
  background: color-mix(in srgb, var(--surface-muted) 88%, var(--accent) 6%);
}

@media (prefers-reduced-motion: no-preference) {
  .benchmark-focus {
    animation: wsc-panel-rise 0.45s var(--ease-out) 0.06s backwards;
  }
}

.benchmark-focus__intro {
  margin: 0 0 0.45rem;
  color: var(--text-primary);
  line-height: 1.55;
}

.benchmark-focus__verdict {
  margin: 0;
  color: var(--text-secondary);
  font-size: 0.92rem;
  line-height: 1.5;
}

.facts-grid {
  display: grid;
  grid-template-columns: minmax(7.5rem, 34%) 1fr;
  gap: 0.4rem 1rem;
  margin: 0 0 0.65rem;
  padding: 0.75rem 0.9rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: var(--surface-muted);
  font-size: 0.9rem;
}

.facts-grid__dt {
  margin: 0;
  color: var(--text-tertiary);
  font-weight: 600;
}

.facts-grid__dd {
  margin: 0;
  color: var(--text-secondary);
  word-break: break-word;
}

.facts-grid__link {
  color: var(--accent);
  font-weight: 600;
  text-decoration: underline;
  text-decoration-color: color-mix(in srgb, var(--accent) 42%, transparent);
  text-decoration-thickness: 1.5px;
  text-underline-offset: 0.12em;
  transition:
    color var(--duration-fast) var(--ease-out),
    text-decoration-color var(--duration-fast) var(--ease-out),
    text-decoration-thickness var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.facts-grid__link:hover {
  color: var(--accent-hover);
  text-decoration-color: color-mix(in srgb, var(--accent-hover) 75%, transparent);
  text-decoration-thickness: 2px;
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
  .facts-grid__link:hover {
    transform: translateY(-1px);
  }
}

@media (max-width: 520px) {
  .facts-grid {
    grid-template-columns: 1fr;
  }

  .facts-grid__dt {
    margin-top: 0.35rem;
  }

  .facts-grid__dt:first-child {
    margin-top: 0;
  }
}

.listing-fit {
  margin: 0 0 1.25rem;
  padding: 1rem 1.05rem 1.1rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: color-mix(in srgb, var(--surface-page) 96%, var(--accent) 4%);
}

.listing-fit__heading {
  font-size: 1rem;
  margin: 0 0 0.65rem;
  letter-spacing: -0.02em;
}

.listing-fit__meter-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.65rem;
  flex-wrap: wrap;
}

.listing-fit__meter {
  flex: 1 1 8rem;
  min-width: 6rem;
  height: 0.45rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--border-strong) 55%, transparent);
  overflow: hidden;
}

.listing-fit__meter-fill {
  height: 100%;
  border-radius: inherit;
  transition: width 0.45s var(--ease-out);
}

.listing-fit__meter-label {
  font-size: 0.8rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--text-secondary);
  white-space: nowrap;
}

.listing-fit__headline {
  margin: 0 0 0.4rem;
  font-size: 0.98rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.35;
}

.listing-fit__body {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.listing-fit--strong {
  border-color: color-mix(in srgb, var(--risk-low) 45%, var(--border-subtle));
  background: color-mix(in srgb, var(--surface-page) 94%, var(--risk-low) 8%);
}

.listing-fit--strong .listing-fit__meter-fill {
  background: linear-gradient(
    90deg,
    color-mix(in srgb, var(--risk-low) 88%, #000),
    color-mix(in srgb, var(--risk-low) 65%, var(--accent))
  );
}

.listing-fit--mixed {
  border-color: color-mix(in srgb, var(--risk-mid) 45%, var(--border-subtle));
  background: color-mix(in srgb, var(--surface-page) 94%, var(--risk-mid) 8%);
}

.listing-fit--mixed .listing-fit__meter-fill {
  background: linear-gradient(
    90deg,
    color-mix(in srgb, var(--risk-mid) 88%, #000),
    color-mix(in srgb, var(--risk-mid) 55%, var(--accent))
  );
}

.listing-fit--weak {
  border-color: color-mix(in srgb, var(--risk-high) 45%, var(--border-subtle));
  background: color-mix(in srgb, var(--surface-page) 94%, var(--risk-high) 8%);
}

.listing-fit--weak .listing-fit__meter-fill {
  background: linear-gradient(
    90deg,
    color-mix(in srgb, var(--risk-high) 85%, #000),
    color-mix(in srgb, var(--risk-high) 50%, var(--accent))
  );
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

.methodology-text {
  white-space: pre-line;
  line-height: 1.55;
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
  touch-action: manipulation;
  transition:
    background var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

@media (hover: hover) and (pointer: fine) {
  .share__btn:hover {
    background: var(--accent-hover);
  }
}

.share__btn:active {
  transform: translateY(1px);
}

@media (max-width: 520px) {
  .share__btn {
    width: 100%;
    min-height: 48px;
    padding: 0.65rem 1rem;
  }
}
</style>
