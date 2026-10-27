/**
 * Merk- en productconstanten (niet voor vertaling — zie i18n voor zichtbare teksten).
 * Gebruikt voor meta (theme-color), analytics-keys, enz.
 */
export default defineAppConfig({
  brand: {
    /** Stabiele id voor events / deep links */
    id: 'huurradar',
    /** Merkgroen (header) — gelijk aan --brand in main.css :root */
    themeColorLight: '#0e3b2e',
    /** Donker: header blijft merkgroen — gelijk aan html.dark --brand */
    themeColorDark: '#0e3b2e',
    ogType: 'website' as const,
  },
})
