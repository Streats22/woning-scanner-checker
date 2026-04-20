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
        NITRO_HOST: '127.0.0.1',
        PORT: '3000',
        NITRO_PORT: '3000'
      },
      autorestart: true,
      watch: false,
      max_restarts: 10,
      restart_delay: 3000
    }
  ]
};
