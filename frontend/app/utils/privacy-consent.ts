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

/** Verwijdert veelvoorkomende Google Analytics-cookies (niet HttpOnly) bij “Alleen noodzakelijk”. */
export function clearGoogleAnalyticsCookies(): void {
  if (typeof document === 'undefined')
    return
  const host = window.location.hostname
  const expire = 'Max-Age=0; path=/; SameSite=Lax'
  const tryClear = (name: string) => {
    document.cookie = `${name}=; ${expire}`
    document.cookie = `${name}=; ${expire}; domain=${host}`
    document.cookie = `${name}=; ${expire}; domain=.${host}`
  }
  try {
    const raw = document.cookie.split(';')
    for (const part of raw) {
      const name = part.trim().split('=')[0]
      if (!name)
        continue
      if (name === '_ga' || name === '_gid' || name === '_gat' || name.startsWith('_ga_')) {
        tryClear(name)
      }
    }
  }
  catch {
    /* ignore */
  }
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
  clearGoogleAnalyticsCookies()
}
