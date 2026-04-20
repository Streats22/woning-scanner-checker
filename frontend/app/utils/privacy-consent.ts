/** Client-side privacy consent storage (AVG/ePrivacy-transparantie). */

export const CONSENT_KEY = 'wsc_privacy_consent'
export const LOCALE_PREF_KEY = 'wsc_locale_pref'
export const LEGACY_I18N_COOKIE = 'wsc_i18n'

export type ConsentRecord = {
  version: number
  functional: boolean
  updatedAt: string
}

export function readConsent(): ConsentRecord | null {
  if (import.meta.server)
    return null
  try {
    const raw = localStorage.getItem(CONSENT_KEY)
    if (!raw)
      return null
    const parsed = JSON.parse(raw) as ConsentRecord
    if (typeof parsed.functional !== 'boolean' || typeof parsed.version !== 'number')
      return null
    return parsed
  }
  catch {
    return null
  }
}

export function writeConsent(functional: boolean): ConsentRecord {
  const record: ConsentRecord = {
    version: 1,
    functional,
    updatedAt: new Date().toISOString(),
  }
  localStorage.setItem(CONSENT_KEY, JSON.stringify(record))
  return record
}

export function clearFunctionalStorage(): void {
  try {
    localStorage.removeItem(LOCALE_PREF_KEY)
    localStorage.removeItem('wsc-color-mode')
  }
  catch {
    /* ignore */
  }
  if (typeof document === 'undefined')
    return
  document.cookie = `${LEGACY_I18N_COOKIE}=; Max-Age=0; path=/; SameSite=Lax`
}
