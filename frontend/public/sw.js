// Zelfvernietigende service worker. Bewust permanent in public/ houden.
// Browsers die nog een oude Workbox-SW op /sw.js hebben, halen deze bij de
// volgende update-check op: hij schrijft zichzelf uit, wist caches en herlaadt
// open tabs. @vite-pwa/nuxt 2.x heeft geen ingebouwde `selfDestroying` meer,
// daarom staat de gegenereerde SW (alleen bij NUXT_PUBLIC_ENABLE_PWA_SW=1)
// onder een andere naam: sw-workbox.js (zie nuxt.config.ts, pwa.filename).
self.addEventListener('install', () => {
  self.skipWaiting()
})
self.addEventListener('activate', (e) => {
  e.waitUntil((async () => {
    await self.registration.unregister()
    const cacheNames = await self.caches.keys()
    await Promise.all(cacheNames.map(name => self.caches.delete(name)))
    const clients = await self.clients.matchAll({ type: 'window' })
    await Promise.all(clients.map(client => client.navigate(client.url).catch(() => {})))
  })())
})
