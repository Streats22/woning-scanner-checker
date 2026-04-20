import en from '../../i18n/locales/en.json'
import nl from '../../i18n/locales/nl.json'

/**
 * Cookie/storage table rows for the consent dialog.
 *
 * Read from locale JSON files directly — not via getLocaleMessage/tm, because
 * @nuxtjs/i18n may compile nested strings to intlify AST objects (shows as JSON garbage in the UI).
 */
export type CookieTableRow = {
  name: string
  type: string
  purpose: string
  duration: string
}

export function getCookieTableRows(locale: string): CookieTableRow[] {
  const raw = locale === 'en' ? en : nl
  const rows = raw.cookies?.rows
  return Array.isArray(rows) ? rows : []
}
