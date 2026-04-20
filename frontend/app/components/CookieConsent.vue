<template>
  <Teleport to="body">
    <Transition name="wsc-cookie-backdrop">
      <div v-if="showDialog" class="cookie-backdrop" aria-hidden="true" />
    </Transition>
    <Transition name="wsc-cookie-panel">
      <div
        v-if="showDialog"
        class="cookie-root"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        :aria-describedby="descId"
      >
        <div class="cookie-panel">
          <h2 :id="titleId" class="cookie-panel__title font-display">{{ t('cookies.dialogTitle') }}</h2>
          <p :id="descId" class="cookie-panel__intro">{{ t('cookies.dialogIntro') }}</p>
          <p class="cookie-panel__no-tracking">{{ t('cookies.noTrackingBanner') }}</p>

          <button
            type="button"
            class="cookie-panel__toggle"
            :aria-expanded="detailsOpen"
            @click="detailsOpen = !detailsOpen"
          >
            {{ detailsOpen ? t('cookies.detailsHide') : t('cookies.detailsToggle') }}
          </button>

          <div v-show="detailsOpen" class="cookie-panel__details">
            <table class="cookie-table">
              <thead>
                <tr>
                  <th scope="col">{{ t('cookies.tableName') }}</th>
                  <th scope="col">{{ t('cookies.tableType') }}</th>
                  <th scope="col">{{ t('cookies.tablePurpose') }}</th>
                  <th scope="col">{{ t('cookies.tableDuration') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in tableRows" :key="i">
                  <td><code class="cookie-table__code">{{ row.name }}</code></td>
                  <td>{{ row.type }}</td>
                  <td>{{ row.purpose }}</td>
                  <td>{{ row.duration }}</td>
                </tr>
              </tbody>
            </table>
            <p class="cookie-panel__api-note">{{ t('cookies.apiNote') }}</p>
            <p class="cookie-panel__fonts-note">{{ t('cookies.fontsNote') }}</p>
            <p class="cookie-panel__legal">{{ t('cookies.legalNote') }}</p>
          </div>

          <div class="cookie-panel__actions">
            <button type="button" class="cookie-panel__btn cookie-panel__btn--secondary" @click="onNecessaryOnly">
              {{ t('cookies.necessaryOnly') }}
            </button>
            <button type="button" class="cookie-panel__btn cookie-panel__btn--primary" @click="onAcceptFunctional">
              {{ t('cookies.acceptFunctional') }}
            </button>
          </div>
          <p class="cookie-panel__hint">{{ t('cookies.necessaryOnlyHelp') }}</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
type CookieRow = {
  name: string
  type: string
  purpose: string
  duration: string
}

const { t, locale, getLocaleMessage } = useI18n()
const { showDialog, acceptFunctional, necessaryOnly } = useCookieConsent()

const titleId = 'wsc-cookie-title'
const descId = 'wsc-cookie-desc'
const detailsOpen = ref(false)

/** Geen `tm()` op geneste objecten: anders AST i.p.v. platte strings. */
const tableRows = computed((): CookieRow[] => {
  const msgs = getLocaleMessage(locale.value) as Record<string, unknown>
  const cookies = msgs?.cookies as { rows?: CookieRow[] } | undefined
  const rows = cookies?.rows
  return Array.isArray(rows) ? rows : []
})

function onAcceptFunctional() {
  acceptFunctional(locale.value)
}

function onNecessaryOnly() {
  necessaryOnly()
}

watch(showDialog, (open) => {
  if (import.meta.client) {
    document.body.style.overflow = open ? 'hidden' : ''
  }
})
</script>

<style scoped>
.font-display {
  font-family: var(--font-display), var(--font-sans);
}

.cookie-backdrop {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: color-mix(in srgb, var(--text-primary) 35%, transparent);
  backdrop-filter: blur(2px);
}

.cookie-root {
  position: fixed;
  inset: 0;
  /* Boven backdrop (200), onder sticky header (250): root zelf heeft geen pointer-events */
  z-index: 210;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 1rem;
  pointer-events: none;
}

.cookie-panel {
  pointer-events: auto;
  width: 100%;
  max-width: 42rem;
  max-height: min(92vh, 40rem);
  overflow-y: auto;
  margin-bottom: env(safe-area-inset-bottom, 0);
  padding: 1.35rem 1.25rem 1.25rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-subtle);
  background: var(--surface-elevated);
  box-shadow: var(--shadow-lg);
}

.cookie-panel__title {
  margin: 0 0 0.35rem;
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--text-primary);
}

.cookie-panel__intro,
.cookie-panel__no-tracking {
  margin: 0 0 0.65rem;
  font-size: 0.9rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.cookie-panel__no-tracking {
  padding: 0.55rem 0.65rem;
  border-radius: var(--radius-sm);
  background: var(--surface-muted);
  border: 1px solid var(--border-subtle);
  color: var(--text-primary);
  font-size: 0.85rem;
}

.cookie-panel__toggle {
  margin: 0.25rem 0 0.75rem;
  padding: 0.35rem 0;
  border: none;
  background: none;
  color: var(--accent);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.cookie-panel__details {
  margin-bottom: 1rem;
}

.cookie-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.78rem;
  color: var(--text-secondary);
}

.cookie-table th,
.cookie-table td {
  border: 1px solid var(--border-subtle);
  padding: 0.45rem 0.5rem;
  text-align: left;
  vertical-align: top;
}

.cookie-table th {
  background: var(--surface-muted);
  color: var(--text-primary);
  font-weight: 600;
}

.cookie-table__code {
  font-size: 0.72rem;
  word-break: break-all;
  color: var(--text-primary);
}

.cookie-panel__api-note,
.cookie-panel__fonts-note,
.cookie-panel__legal {
  margin: 0.65rem 0 0;
  font-size: 0.78rem;
  line-height: 1.5;
  color: var(--text-tertiary);
}

.cookie-panel__actions {
  display: flex;
  flex-direction: column-reverse;
  gap: 0.5rem;
}

@media (min-width: 480px) {
  .cookie-panel__actions {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
}

.cookie-panel__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2.65rem;
  padding: 0.55rem 1rem;
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: var(--radius-md);
  cursor: pointer;
  border: none;
  transition:
    background var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out);
}

.cookie-panel__btn--primary {
  background: var(--accent);
  color: #fff;
  box-shadow: var(--shadow-sm);
}

.cookie-panel__btn--primary:hover {
  background: var(--accent-hover);
}

.cookie-panel__btn--secondary {
  background: var(--surface-muted);
  color: var(--text-primary);
  border: 1px solid var(--border-strong);
}

.cookie-panel__btn--secondary:hover {
  background: color-mix(in srgb, var(--surface-muted) 85%, var(--accent-muted));
}

.cookie-panel__hint {
  margin: 0.45rem 0 0;
  font-size: 0.75rem;
  color: var(--text-tertiary);
  line-height: 1.45;
}

.wsc-cookie-backdrop-enter-active,
.wsc-cookie-backdrop-leave-active {
  transition: opacity var(--duration-normal) var(--ease-out);
}

.wsc-cookie-backdrop-enter-from,
.wsc-cookie-backdrop-leave-to {
  opacity: 0;
}

.wsc-cookie-panel-enter-active,
.wsc-cookie-panel-leave-active {
  transition:
    opacity var(--duration-normal) var(--ease-out),
    transform var(--duration-slow) var(--ease-out);
}

.wsc-cookie-panel-enter-from,
.wsc-cookie-panel-leave-to {
  opacity: 0;
  transform: translateY(0.75rem);
}

@media (prefers-reduced-motion: reduce) {
  .wsc-cookie-panel-enter-from,
  .wsc-cookie-panel-leave-to {
    transform: none;
  }
}
</style>
