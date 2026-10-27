<template>
  <div class="method">
    <template v-for="(block, i) in blocks" :key="i">
      <section v-if="block.kind === 'step'" class="method-step">
        <span class="method-step__num" aria-hidden="true">{{ block.num }}</span>
        <div class="method-step__body">
          <h3 class="method-step__title">
            <span class="sr-only">{{ block.label }} {{ block.num }}: </span>{{ block.title }}
          </h3>
          <p v-if="block.text" class="method-step__text">{{ block.text }}</p>
        </div>
      </section>

      <section v-else-if="block.kind === 'group'" class="method-group">
        <h4 class="method-group__title">{{ block.title }}</h4>
        <ul class="method-group__list">
          <li v-for="(item, j) in block.items" :key="j" class="method-group__item">
            <template v-for="(part, k) in item" :key="k">
              <span v-if="part.points" class="method-group__pts">{{ part.text }}</span>
              <template v-else>{{ part.text }}</template>
            </template>
          </li>
        </ul>
      </section>

      <p v-else class="method-note">{{ block.text }}</p>
    </template>
  </div>
</template>

<script setup lang="ts">
/**
 * Zet de methodologie-tekst van de API (lang/{nl,en}/report_enrichment.php) om in leesbare blokken:
 * "Stap 1b — titel: uitleg" → stapkaart, een kopregel met "–"-regels → lijst met puntenbadges,
 * de rest → losse alinea.
 */
type Part = { text: string; points: boolean }
type Block =
  | { kind: 'step'; label: string; num: string; title: string; text: string }
  | { kind: 'group'; title: string; items: Part[][] }
  | { kind: 'note'; text: string }

const props = defineProps<{ text: string }>()

const STEP_RE = /^(Stap|Step)\s+(\d+[a-z]?)\s*(?:—\s*([^:]+?)|\(([^)]+)\))?\s*:\s*([\s\S]*)$/
const ITEM_RE = /^[–-]\s*/
const POINTS_RE = /(\+\d+)/g

function splitPoints(line: string): Part[] {
  return line
    .split(POINTS_RE)
    .filter(s => s !== '')
    .map(s => ({ text: s, points: /^\+\d+$/.test(s) }))
}

function capitalize(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const blocks = computed<Block[]>(() =>
  props.text
    .replace(/\r\n/g, '\n')
    .split(/\n\s*\n/)
    .map(b => b.trim())
    .filter(Boolean)
    .map((chunk): Block => {
      const lines = chunk.split('\n').map(l => l.trim()).filter(Boolean)
      const step = chunk.match(STEP_RE)
      if (step) {
        const title = (step[3] ?? step[4] ?? '').trim()
        return { kind: 'step', label: step[1]!, num: step[2]!, title: capitalize(title), text: capitalize(step[5]!.trim()) }
      }
      if (lines.length > 1 && lines.slice(1).every(l => ITEM_RE.test(l))) {
        return {
          kind: 'group',
          title: lines[0]!,
          items: lines.slice(1).map(l => splitPoints(capitalize(l.replace(ITEM_RE, '')))),
        }
      }
      return { kind: 'note', text: chunk }
    }),
)
</script>

<style scoped>
.method {
  display: grid;
  gap: 0.6rem;
}

.method-step {
  display: grid;
  grid-template-columns: 2.4rem 1fr;
  gap: 0.85rem;
  align-items: start;
  padding: 1rem 1.1rem;
  border-radius: var(--radius-md);
  background: var(--surface-elevated);
}

.method-step__num {
  width: 2.4rem;
  height: 2.4rem;
  border-radius: 50%;
  background: var(--brand);
  color: var(--brand-soft);
  font-weight: 800;
  font-size: 0.95rem;
  display: grid;
  place-items: center;
}

.method-step__title {
  margin: 0.35rem 0 0.3rem;
  font-size: 1.02rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  line-height: 1.3;
  color: var(--text-primary);
}

.method-step__text {
  margin: 0;
  max-width: 70ch;
  line-height: 1.55;
  color: var(--text-secondary);
}

.method-group {
  margin-left: 3.25rem;
  padding: 0.9rem 1.1rem;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border-subtle);
}

.method-group__title {
  margin: 0 0 0.55rem;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--text-primary);
}

.method-group__list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.45rem;
}

.method-group__item {
  position: relative;
  padding-left: 1.1rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.method-group__item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.55rem;
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 50%;
  background: var(--action);
}

.method-group__pts {
  display: inline-block;
  margin: 0 0.15rem;
  padding: 0 0.45rem;
  border-radius: 999px;
  background: var(--risk-high-bg);
  color: var(--risk-high);
  font-size: 0.85rem;
  font-weight: 800;
  line-height: 1.5;
}

.method-note {
  margin: 0.4rem 0 0;
  font-size: 0.9rem;
  line-height: 1.5;
  color: var(--text-tertiary);
}

@media (max-width: 520px) {
  .method-group {
    margin-left: 0;
  }
}
</style>
