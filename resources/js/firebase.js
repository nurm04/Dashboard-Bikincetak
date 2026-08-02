import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import axios from 'axios';

// TODO: Paste firebaseConfig lu lagi di sini
const firebaseConfig = {
    apiKey: "AIzaSyBq0FYqqrZZ24kEhKk8StX9EbtpcW9dOZ8",
    authDomain: "bikincetak-e4b8a.firebaseapp.com",
    projectId: "bikincetak-e4b8a",
    storageBucket: "bikincetak-e4b8a.firebasestorage.app",
    messagingSenderId: "104243308670",
    appId: "1:104243308670:web:9803b4104b063b563d502f",
    measurementId: "G-2G01GKPKSF"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export const requestFirebaseNotificationPermission = async () => {
    try {
        console.log('Meminta izin notifikasi...');
        const permission = await Notification.requestPermission();

        if (permission === 'granted') {
            console.log('Izin diberikan!');

            // TODO: Paste VAPID Key lu di bagian vapidKey ini
            const token = await getToken(messaging, {
                vapidKey: 'BPHz6e14zo3fLGqDlykMlgJh32_eMLiDM9Gvjh4if1MFq2fgtkuD9BiwSiSP9Mu1PTSbWxCgg2Lgy3Lh49-PZo0'
            });

            if (token) {
                console.log('FCM Token didapat:', token);
                // Tembak tokennya ke backend Laravel via Axios
                await axios.post('/simpan-fcm-token', { token: token });
                console.log('Token berhasil disimpan di database!');
            } else {
                console.log('Gagal mendapatkan token FCM.');
            }
        } else {
            console.log('Izin notifikasi ditolak.');
        }
    } catch (error) {
        console.error('Error saat setup notifikasi:', error);
    }
};

// Menangkap notifikasi saat web BikinCetak sedang dibuka (Foreground)
export const onMessageListener = () =>
    new Promise((resolve) => {
        onMessage(messaging, (payload) => {
            resolve(payload);
        });
    });
