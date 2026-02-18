const CACHE_NAME = 'strikecircle-v1';
const ASSETS = [
  '/',
  '/assets/css/app.css',
  '/assets/js/api.js',
  '/assets/js/auth.js',
  '/assets/js/feed.js',
  '/assets/js/leagues.js',
  '/assets/js/tournaments.js',
  '/assets/js/messages.js',
  '/assets/js/scores.js',
  '/manifest.json',
];
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
});
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
    ))
  );
});
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(resp => resp || fetch(event.request))
  );
});
