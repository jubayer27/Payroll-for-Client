const CACHE_NAME = 'staff-app-v1';
const urlsToCache = [
  'staff/index.php',
  'staff/scan.php',
  'staff/history.php',
  'staff/salary.php',
  'attender/attender_dashboard.php'
];

// Install Event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

// Fetch Event (Network First, fall back to Cache)
self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});