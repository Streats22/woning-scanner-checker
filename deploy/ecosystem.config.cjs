/**
 * PM2 op Ploi: Nuxt Nitro op 127.0.0.1:3000 (zelfde poort als deploy/dehuurradar.nl.conf).
 *
 * Eenmalig op de server (pas `cwd` aan naar jouw pad naar frontend/):
 *   cd /home/ploi/dehuurradar.nl/frontend && npm ci && npm run build
 *   pm2 start /path/to/repo/deploy/ecosystem.config.cjs
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
            },
        },
    ],
}
