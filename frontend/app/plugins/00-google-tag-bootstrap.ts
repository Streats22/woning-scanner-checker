/**
 * Zet de officiële gtag-snippet in <head> (SSR + client) zodat Google Tag Assistant / GA-setup de tag zien.
 * Consent Mode: standaard analytics_storage denied; echte hits via 01-google-analytics-consent.client.ts
 * (functioneel akkoord, of tijdelijk `gaDefaultApproved` zonder te wachten op de banner).
 */
export default defineNuxtPlugin(() => {
  const id = useRuntimeConfig().public.googleAnalyticsId?.trim()
  if (!id)
    return

  const safeId = id.replace(/[^A-Z0-9_-]/gi, '')
  const inline = [
    'window.dataLayer=window.dataLayer||[];',
    'function gtag(){dataLayer.push(arguments);}',
    'window.gtag=gtag;',
    "gtag('js', new Date());",
    "gtag('consent','default',{'analytics_storage':'denied','ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied'});",
    `gtag('config','${safeId}',{send_page_view:false});`,
  ].join('')

  useHead({
    script: [
      {
        key: 'google-gtag-js',
        async: true,
        src: `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(safeId)}`,
      },
      {
        key: 'google-gtag-inline',
        type: 'text/javascript',
        innerHTML: inline,
      },
    ],
  })
})
