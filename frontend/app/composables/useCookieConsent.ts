import {
  clearFunctionalStorage,
  LOCALE_PREF_KEY,
  readConsent,
  writeConsent,
  type ConsentRecord,
} from '~/utils/privacy-consent'

/**
 * AVG/ePrivacy: gedeelde state (useState) voor cookiemelding + footer-link.
 */
export function useCookieConsent() {
  const consent = useState<ConsentRecord | null>('wsc-privacy-consent', () => null)
  const settingsOpen = useState<boolean>('wsc-cookie-settings-open', () => false)

  const needsChoice = computed(() => consent.value === null)

  const showDialog = computed(() => needsChoice.value || settingsOpen.value)

  function acceptFunctional(currentLocale: string) {
    const record = writeConsent(true)
    consent.value = record
    if (currentLocale === 'nl' || currentLocale === 'en') {
      try {
        localStorage.setItem(LOCALE_PREF_KEY, currentLocale)
      }
      catch {
        /* ignore */
      }
    }
    settingsOpen.value = false
  }

  function necessaryOnly() {
    clearFunctionalStorage()
    const record = writeConsent(false)
    consent.value = record
    settingsOpen.value = false
    if (import.meta.client)
      window.location.reload()
  }

  function openCookieSettings() {
    settingsOpen.value = true
  }

  return {
    consent,
    needsChoice,
    settingsOpen,
    showDialog,
    acceptFunctional,
    necessaryOnly,
    openCookieSettings,
  }
}
