<?php
namespace App\Traits;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

trait FcmNotificationTrait
{
    public function sendFcmNotification($user, $title, $body, $url = '/', $data = [])
    {
        $tokens = $user->fcm_token;

        // Pastikan token ada dan bentuknya array
        if (empty($tokens) || !is_array($tokens)) {
            Log::warning("User ID {$user->id} tidak memiliki FCM Token yang valid.");
            return false;
        }

        try {
            $messaging = Firebase::messaging();

            $message = CloudMessage::fromArray([
                // HAPUS KEY 'notification' DI SINI
                'data' => array_merge([
                    'title' => $title, // Pindahkan title ke sini
                    'body' => $body,   // Pindahkan body ke sini
                    'url' => $url,
                    'tipe' => 'pesanan',
                    'kode' => ''
                ], $data),
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high'
                    ]
                ],
            ]);

            // Tembak ke SEMUA device sekaligus
            $report = $messaging->sendMulticast($message, $tokens);

            // Tembak ke SEMUA device sekaligus
            $report = $messaging->sendMulticast($message, $tokens);

            // BONUS: Auto-Cleanup Token Mati (Biar database gak penuh sama token invalid)
            if ($report->hasFailures()) {
                $invalidTokens = [];
                
                foreach ($report->failures()->getItems() as $failure) {
                    $invalidTokens[] = $failure->target()->value();
                }

                // Hapus token yang gagal dari database
                if (!empty($invalidTokens)) {
                    $validTokens = array_diff($tokens, $invalidTokens);
                    $user->update(['fcm_token' => array_values($validTokens)]);
                    Log::info("Menghapus " . count($invalidTokens) . " token FCM invalid untuk User ID {$user->id}");
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
