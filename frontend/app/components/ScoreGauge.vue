<template>
  <svg
    class="score-gauge"
    :class="`score-gauge--${level}`"
    viewBox="0 0 260 150"
    :width="width"
    role="img"
    :aria-label="ariaLabel"
  >
    <path
      class="score-gauge__track"
      d="M30 130 A100 100 0 0 1 230 130"
      fill="none"
      stroke-width="22"
      stroke-linecap="round"
    />
    <path
      class="score-gauge__fill"
      d="M30 130 A100 100 0 0 1 230 130"
      fill="none"
      stroke-width="22"
      stroke-linecap="round"
      pathLength="100"
      :stroke-dasharray="`${clamped} 200`"
    />
    <text
      class="score-gauge__num"
      x="130"
      y="124"
      text-anchor="middle"
    >{{ shown }}</text>
  </svg>
</template>

<script setup lang="ts">
/**
 * Halve-cirkel scoremeter (0–100). Kleur volgt dezelfde drempels als de risicolabels:
 * 0–30 laag, 31–60 matig, 61–100 verhoogd.
 */
const props = withDefaults(
  defineProps<{
    score: number
    ariaLabel: string
    width?: number | string
  }>(),
  {
    width: 220,
  },
)

const clamped = computed(() => Math.max(0, Math.min(100, Math.round(props.score))))

/** Getal telt mee op met de meter; bij reduced motion of op de server direct de eindwaarde. */
const shown = ref(clamped.value)
let raf = 0

function countUp(to: number) {
  if (!import.meta.client || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    shown.value = to
    return
  }
  cancelAnimationFrame(raf)
  const start = performance.now()
  const delay = 100
  const duration = 900
  const tick = (now: number) => {
    const t = Math.min(Math.max((now - start - delay) / duration, 0), 1)
    shown.value = Math.round(to * (1 - Math.pow(1 - t, 3)))
    if (t < 1)
      raf = requestAnimationFrame(tick)
  }
  shown.value = 0
  raf = requestAnimationFrame(tick)
}

onMounted(() => countUp(clamped.value))
watch(clamped, v => countUp(v))
onBeforeUnmount(() => cancelAnimationFrame(raf))

const level = computed(() => {
  if (clamped.value <= 30)
    return 'low'
  if (clamped.value <= 60)
    return 'mid'
  return 'high'
})
</script>

<style scoped>
.score-gauge {
  display: block;
  max-width: 100%;
  height: auto;
}

.score-gauge__track {
  stroke: var(--meter-track);
}

.score-gauge__fill {
  stroke: var(--risk-high-meter);
}

.score-gauge--low .score-gauge__fill {
  stroke: var(--risk-low-meter);
}

.score-gauge--mid .score-gauge__fill {
  stroke: var(--risk-mid-meter);
}

@media (prefers-reduced-motion: no-preference) {
  .score-gauge__fill {
    animation: score-gauge-fill 1s var(--ease-out) 0.1s backwards;
  }
}

@keyframes score-gauge-fill {
  from {
    stroke-dasharray: 0 200;
  }
}

.score-gauge__num {
  font-family: var(--font-display);
  font-size: 64px;
  font-weight: 800;
  letter-spacing: -3px;
  fill: currentColor;
}
</style>
