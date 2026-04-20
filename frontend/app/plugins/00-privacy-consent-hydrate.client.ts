import { readConsent } from '~/utils/privacy-consent'

export default defineNuxtPlugin(() => {
  const consent = useState<ReturnType<typeof readConsent>>('wsc-privacy-consent', () => null)
  consent.value = readConsent()
})
