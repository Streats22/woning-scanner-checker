/**
 * PM2 op Ploi: Nuxt Nitro op 127.0.0.1:3000 (zelfde poort als nginx `proxy_pass`).
 *
 * Elke Ploi-site heeft een eigen map, eigen `frontend/` en eigen PM2-process.
 * Code wijzigen + `git pull` is niet genoeg: altijd `npm run build` in die `frontend/`
 * en daarna `pm2 restart` — zie `deploy/ploi-rebuild-frontend.sh`.
 *
 * Belangrijk: variabelen in de Ploi/Laravel-site-omgeving gaan naar PHP, niet naar Node.
 * `NUXT_PUBLIC_GOOGLE_ANALYTICS_ID` moet in `frontend/.env` op de server staan (zelfde bestand
 * als bij `npm run build`). Die waarden worden hieronder ingelezen zodat PM2 ze ook aan Nitro geeft.
 *
 * Test-site (humble-shore-….ploi.website): zet `cwd` naar die site, zelfde poort als
 * in nginx voor die site (vaak 3000 als er maar één Nuxt-app op de server draait).
 *
 * Eenmalig:
 *   cd /home/ploi/<site>/frontend && npm ci && npm run build
 *   pm2 start /path/to/deploy/ecosystem.config.cjs
 *   pm2 save && pm2 startup
 *
 * Check: ss -tlnp | grep 3000
 */
const fs = require('node:fs')
const path = require('node:path')

/** Simpele KEY=value parser voor `frontend/.env` (geen multiline-waarden). */
function parseDotenv(filePath) {
  const out = {}
  if (!fs.existsSync(filePath))
    return out
  const text = fs.readFileSync(filePath, 'utf8')
  for (const line of text.split('\n')) {
    const trimmed = line.trim()
    if (!trimmed || trimmed.startsWith('#'))
      continue
    const eq = trimmed.indexOf('=')
    if (eq <= 0)
      continue
    const key = trimmed.slice(0, eq).trim()
    let val = trimmed.slice(eq + 1).trim()
    if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith('\'') && val.endsWith('\'')))
      val = val.slice(1, -1)
    out[key] = val
  }
  return out
}

const frontendEnvPath = path.join(__dirname, '..', 'frontend', '.env')
const envFromFrontendFile = parseDotenv(frontendEnvPath)

module.exports = {
    apps: [
        {
            name: 'dehuurradar-nuxt',
            cwd: '/home/ploi/dehuurradar.nl/frontend',
            script: '.output/server/index.mjs',
            interpreter: 'node',
            instances: 1,
            exec_mode: 'fork',
            env: {
                ...envFromFrontendFile,
                NODE_ENV: 'production',
                HOST: '127.0.0.1',
                PORT: '3000',
                // Canonieke URL; na wijziging: opnieuw `npm run build`.
                // Geen NUXT_PUBLIC_API_BASE: leeg = relatieve /api/... (zelfde scheme/host als de pagina, geen mixed content).
                NUXT_PUBLIC_SITE_URL: 'https://dehuurradar.nl',
            },
        },
    ],
}
