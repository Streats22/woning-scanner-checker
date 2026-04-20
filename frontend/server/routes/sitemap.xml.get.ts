import type { H3Event } from 'h3'
import { getRequestHeader } from 'h3'

function escapeXml(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;')
}

function resolveBaseUrl(event: H3Event): string {
  const config = useRuntimeConfig(event)
  const fromEnv = (config.public.siteUrl as string)?.trim().replace(/\/$/, '') || ''
  if (fromEnv)
    return fromEnv

  const host = getRequestHeader(event, 'x-forwarded-host') || getRequestHeader(event, 'host') || ''
  const proto = getRequestHeader(event, 'x-forwarded-proto') || 'https'
  return host ? `${proto}://${host}` : ''
}

export default defineEventHandler((event) => {
  const base = resolveBaseUrl(event)

  setResponseHeader(event, 'content-type', 'application/xml; charset=utf-8')

  if (!base) {
    return `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>`
  }

  const entries: { loc: string; changefreq: string; priority: string }[] = [
    { loc: `${base}/`, changefreq: 'weekly', priority: '1.0' },
    { loc: `${base}/check`, changefreq: 'weekly', priority: '0.95' },
    { loc: `${base}/faq`, changefreq: 'monthly', priority: '0.85' },
  ]

  const body = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${entries
  .map(
    e => `  <url>
    <loc>${escapeXml(e.loc)}</loc>
    <changefreq>${e.changefreq}</changefreq>
    <priority>${e.priority}</priority>
  </url>`,
  )
  .join('\n')}
</urlset>`

  return body
})
