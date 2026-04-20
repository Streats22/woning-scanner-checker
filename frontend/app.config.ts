/**
 * Merk- en productconstanten (niet voor vertaling — zie i18n voor zichtbare teksten).
 * Gebruikt voor meta (theme-color), analytics-keys, enz.
 */
export default defineAppConfig({
  brand: {
    /** Stabiele id voor events / deep links */
    id: 'huurradar',
    /** Primair accent (licht) — gelijk aan --accent in main.css :root */
    themeColorLight: '#1e40af',
    /** Achtergrond (donker) — gelijk aan html.dark --surface-page */
    themeColorDark: '#0a101d',
    ogType: 'website' as const,
  },
})
