<template>
  <svg
    class="brand-mark"
    :width="size"
    :height="size"
    viewBox="0 0 40 40"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    :aria-hidden="decorative ? 'true' : undefined"
    :role="decorative ? undefined : 'img'"
  >
    <title v-if="!decorative">{{ title }}</title>
    <rect width="40" height="40" rx="12" class="brand-mark__bg" />
    <g
      stroke="currentColor"
      fill="none"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <circle
        cx="20"
        cy="20"
        r="11"
        stroke-width="1.25"
        opacity="0.38"
      />
      <circle
        cx="20"
        cy="20"
        r="6.5"
        stroke-width="1.25"
        opacity="0.52"
      />
      <path
        d="M20 20V9.5"
        stroke-width="2"
      />
      <path
        d="M20 20L29 14"
        stroke-width="1.5"
        opacity="0.42"
      />
    </g>
    <circle
      cx="25"
      cy="14"
      r="2.25"
      fill="currentColor"
      opacity="0.92"
    />
  </svg>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    /** Pixel size (square) */
    size?: number
    /** Als true: verberg voor screenreaders (duplicaat van zichtbare naam ernaast) */
    decorative?: boolean
    title?: string
  }>(),
  {
    size: 40,
    decorative: true,
    title: 'De Huur Radar',
  },
)
</script>

<style scoped>
.brand-mark {
  flex-shrink: 0;
  color: var(--accent);
}

.brand-mark__bg {
  fill: var(--accent-muted);
}
</style>
