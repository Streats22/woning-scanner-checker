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
      class="brand-mark__lines"
      stroke="currentColor"
      fill="none"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <circle
        cx="20"
        cy="20"
        r="11"
        stroke-width="1.4"
        opacity="0.5"
      />
      <circle
        cx="20"
        cy="20"
        r="6.5"
        stroke-width="1.4"
        opacity="0.7"
      />
      <path
        d="M20 20V9.5"
        stroke-width="2.2"
      />
      <path
        d="M20 20L29 14"
        stroke-width="1.6"
        opacity="0.55"
      />
    </g>
    <circle
      cx="25"
      cy="14"
      r="2.5"
      class="brand-mark__dot"
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
/* Mint vlak, woudgroene lijnen, koraal stip — ook op de groene header (vlak blijft mint). */
.brand-mark {
  flex-shrink: 0;
  color: #0e3b2e;
}

.brand-mark__bg {
  fill: #c8f2dc;
}

.brand-mark__dot {
  fill: #ff7a59;
}
</style>
