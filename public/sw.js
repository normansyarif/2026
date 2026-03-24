const CACHE_NAME = 'habit-shell-v1';
const BASE_PATH = self.location.pathname.replace(/\/[^/]+$/, '');

function withBase(path = '') {
    const normalizedPath = path === '' ? '/' : `/${path.replace(/^\/+/, '')}`;
    return `${BASE_PATH}${normalizedPath}`;
}

const APP_SHELL = [
    withBase(''),
    withBase('today'),
    withBase('habits'),
    withBase('settings'),
    withBase('manifest.webmanifest'),
    withBase('icons/icon-192.png'),
    withBase('icons/icon-512.png'),
    withBase('icons/apple-touch-icon.png'),
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(withBase('')))
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                    return networkResponse;
                }

                const responseClone = networkResponse.clone();

                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));

                return networkResponse;
            });
        })
    );
});
