importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyBq0FYqqrZZ24kEhKk8StX9EbtpcW9dOZ8",
  authDomain: "bikincetak-e4b8a.firebaseapp.com",
  projectId: "bikincetak-e4b8a",
  storageBucket: "bikincetak-e4b8a.firebasestorage.app",
  messagingSenderId: "104243308670",
  appId: "1:104243308670:web:9803b4104b063b563d502f",
  measurementId: "G-2G01GKPKSF"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Background message: ', payload);

  // Perhatikan: Kita ambil dari payload.data, bukan notification
  const title = payload.data.title; 
  const options = {
    body: payload.data.body,
    icon: '/logo.png',
    badge: '/logo.png',
    vibrate: [200, 100, 200, 100, 200],
    requireInteraction: true,
    data: {
      url: payload.data.url
    }
  };

  // Munculkan notifikasi ke layar HP secara paksa
  return self.registration.showNotification(title, options);
});

// Event ketika notifikasi di-klik
self.addEventListener('notificationclick', function(event) {
  event.notification.close();

  const urlToOpen = new URL(event.notification.data.url, self.location.origin).href;

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (let i = 0; i < windowClients.length; i++) {
        if (windowClients[i].url === urlToOpen && 'focus' in windowClients[i]) {
          return windowClients[i].focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});
