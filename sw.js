const CACHE_NAME = 'techtext-v1.0.1';
const STATIC_ASSETS = [
  './',
  './index.php',
  './app.js',
  './manifest.json',
  './offline.html',
  './icons/icon.svg',
  './icons/icon.php?size=72',
  './icons/icon.php?size=96',
  './icons/icon.php?size=128',
  './icons/icon.php?size=144',
  './icons/icon.php?size=152',
  './icons/icon.php?size=192',
  './icons/icon.php?size=384',
  './icons/icon.php?size=512'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('[SW] Skip waiting');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[SW] Cache failed', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating...');
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((name) => name !== CACHE_NAME)
            .map((name) => {
              console.log('[SW] Deleting old cache', name);
              return caches.delete(name);
            })
        );
      })
      .then(() => {
        console.log('[SW] Claiming clients');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip API calls - always fetch fresh data
  if (event.request.url.includes('/api.php')) {
    return;
  }

  // Skip icon.php requests - let them be generated dynamically
  if (event.request.url.includes('icon.php')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version or fetch from network
        if (response) {
          return response;
        }

        return fetch(event.request)
          .then((networkResponse) => {
            // Don't cache if not valid response
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }

            // Clone response to cache it
            const responseToCache = networkResponse.clone();
            
            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });

            return networkResponse;
          })
          .catch((error) => {
            console.error('[SW] Fetch failed:', error);
            // Return offline page if available for HTML requests
            if (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html')) {
              return caches.match('./offline.html');
            }
          });
      })
  );
});

// Background sync for offline conversions
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-conversions') {
    event.waitUntil(syncPendingConversions());
  }
});

// Push notifications (for future enhancement)
self.addEventListener('push', (event) => {
  const options = {
    body: event.data.text(),
    icon: './icons/icon.php?size=192',
    badge: './icons/icon.php?size=72',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'convert',
        title: 'Convert',
        icon: './icons/icon.php?size=96'
      },
      {
        action: 'close',
        title: 'Close',
        icon: './icons/icon.php?size=96'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('TechText', options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'convert') {
    event.waitUntil(
      clients.openWindow('./')
    );
  }
});

// Function to sync pending conversions
async function syncPendingConversions() {
  console.log('[SW] Syncing pending conversions...');
}

// Message handler from client
self.addEventListener('message', (event) => {
  if (event.data === 'skipWaiting') {
    self.skipWaiting();
  }
});