import enRaw from '../../i18n/locales/en.json?raw'
import nlRaw from '../../i18n/locales/nl.json?raw'

/**
 * Cookie/storage table rows for the consent dialog.
 *
 * Must use `?raw` + JSON.parse: direct `import … from '…/en.json'` is compiled by
 * @nuxtjs/i18n / @intlify to AST objects (`{ t, b, s }`), which render as JSON garbage in the UI.
 */
export type CookieTableRow = {
  name: string
  type: string
  purpose: string
  duration: string
}

type LocaleJson = { cookies?: { rows?: unknown[] } }

const en = JSON.parse(enRaw as string) as LocaleJson
const nl = JSON.parse(nlRaw as string) as LocaleJson

/** Unwrap intlify message AST or pass through plain strings (defensive). */
function coerceLocaleString(v: unknown): string {
  if (typeof v === 'string') {
    return v
  }
  if (v == null) {
    return ''
  }
  if (typeof v !== 'object') {
    return String(v)
  }
  const o = v as Record<string, unknown>
  if (typeof o.s === 'string') {
    return o.s
  }
  if (o.b && typeof o.b === 'object') {
    return coerceLocaleString(o.b)
  }
  return ''
}

function normalizeRow(raw: unknown): CookieTableRow | null {
  if (!raw || typeof raw !== 'object') {
    return null
  }
  const r = raw as Record<string, unknown>
  return {
    name: coerceLocaleString(r.name),
    type: coerceLocaleString(r.type),
    purpose: coerceLocaleString(r.purpose),
    duration: coerceLocaleString(r.duration),
  }
}

export function getCookieTableRows(locale: string): CookieTableRow[] {
  const raw = locale === 'en' ? en : nl
  const rows = raw.cookies?.rows
  if (!Array.isArray(rows)) {
    return []
  }
  return rows.map(normalizeRow).filter((row): row is CookieTableRow => row !== null)
}
