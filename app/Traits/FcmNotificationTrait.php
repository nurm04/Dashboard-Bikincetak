<?php

namespace App\Traits;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

trait FcmNotificationTrait
{
    /**
     * Kirim Push Notification via FCM
     */
    public function sendFcmNotification($user, $title, $body, $url = '/')
    {
        // Cek apakah user punya token
        if (!$user->fcm_token) {
            Log::warning("User ID {$user->id} tidak memiliki FCM Token.");
            return false;
        }

        try {
            $messaging = Firebase::messaging();

            // === INI YANG DIUBAH (Pakai fromArray) ===
            $message = CloudMessage::fromArray([
                'token' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'url' => $url,
                    'tipe' => 'notif_sistem' // Bisa disesuaikan buat trigger warna toast di Vue
                ],
            ]);

            $messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
