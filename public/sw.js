// SPK Shabat Service Worker
const CACHE_NAME = 'spkshabat-v1';

self.addEventListener('install', (e) => {
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(clients.claim());
});

// Handle push notification
self.addEventListener('push', (e) => {
    if (!e.data) return;

    let data;
    try {
        data = e.data.json();
    } catch {
        data = { title: 'SPK Shabat', body: e.data.text(), url: '/' };
    }

    const options = {
        body:    data.body || '',
        icon:    data.icon  || '/icon-192.png',
        badge:   data.badge || '/icon-192.png',
        vibrate: [200, 100, 200],
        data:    { url: data.url || '/' },
        actions: [
            { action: 'open',    title: 'Buka SPK' },
            { action: 'dismiss', title: 'Tutup'    },
        ],
        requireInteraction: true,
    };

    e.waitUntil(
        self.registration.showNotification(data.title || 'SPK Shabat', options)
    );
});

// Handle notification click
self.addEventListener('notificationclick', (e) => {
    e.notification.close();

    if (e.action === 'dismiss') return;

    const url = e.notification.data?.url || '/';

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Kalau sudah ada tab yang buka app, fokus ke sana
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.focus();
                    client.navigate(url);
                    return;
                }
            }
            // Buka tab baru
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
