import type { H3Event } from 'h3'
import { getRequestHeader } from 'h3'

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
  setResponseHeader(event, 'content-type', 'text/plain; charset=utf-8')

  const lines = [
    'User-agent: *',
    'Allow: /',
    '',
  ]

  if (base)
    lines.push(`Sitemap: ${base}/sitemap.xml`, '')

  return lines.join('\n')
})
