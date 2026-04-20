/**
 * Normaliseert de Laravel API-basis-URL voor de browser.
 * - Zowel `http://` als `https://` zijn toegestaan; bij niet-lokale hosts wordt voorkeur gegeven aan HTTPS
 *   (mixed content: HTTPS-site + `http://`-API wordt door de browser geblokkeerd).
 * - Lokaal (localhost, 127.0.0.1, *.test) blijft ongewijzigd.
 * - Zet `NUXT_PUBLIC_API_ALLOW_HTTP=1` om nooit naar HTTPS te upgraden (zeldzaam; alleen als je API echt alleen HTTP heeft).
 */
export function normalizeApiBase(
  raw: string,
  opts?: { serverProductionBuild?: boolean; allowHttp?: boolean },
): string {
  const t = String(raw ?? '').trim()
  if (!t) return ''
  if (opts?.allowHttp === true) return t
  if (!t.startsWith('http://')) return t
  try {
    const u = new URL(t)
    const host = u.hostname
    const isLocal =
      host === 'localhost'
      || host === '127.0.0.1'
      || host.endsWith('.test')
    if (isLocal) return t

    const onHttpsPage =
      typeof window !== 'undefined' && window.location?.protocol === 'https:'

    if (onHttpsPage || opts?.serverProductionBuild) {
      u.protocol = 'https:'
      return u.origin
    }
  }
  catch {
    return t
  }
  return t
}
