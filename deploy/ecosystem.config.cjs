/**
 * PM2 op Ploi: Nuxt Nitro op 127.0.0.1:3000 (zelfde poort als nginx `proxy_pass`).
 *
 * Elke Ploi-site heeft een eigen map, eigen `frontend/` en eigen PM2-process.
 * Code wijzigen + `git pull` is niet genoeg: altijd `npm run build` in die `frontend/`
 * en daarna `pm2 restart` — zie `deploy/ploi-rebuild-frontend.sh`.
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
                NODE_ENV: 'production',
                HOST: '127.0.0.1',
                PORT: '3000',
                // Canonieke URL + API (zelfde domein); na wijziging: opnieuw `npm run build`.
                NUXT_PUBLIC_SITE_URL: 'https://dehuurradar.nl',
                NUXT_PUBLIC_API_BASE: 'https://dehuurradar.nl',
            },
        },
    ],
}
