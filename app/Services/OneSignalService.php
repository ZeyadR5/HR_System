<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    public static function sendToUser($userId, string $title, string $message): bool
    {
        $appId = config('services.onesignal.app_id', env('ONESIGNAL_APP_ID', '9d306f1d-5c6d-4d06-91ed-0395ed04a46a'));
        $apiKey = config('services.onesignal.rest_api_key', env('ONESIGNAL_REST_API_KEY'));

        if (empty($appId) || empty($apiKey)) {
            Log::warning('OneSignal skipped: missing APP_ID or REST_API_KEY');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(6)->post('https://api.onesignal.com/notifications', [
                'app_id' => $appId,
                'include_aliases' => [
                    'external_id' => [(string) $userId]
                ],
                'target_channel' => 'push',
                'headings' => ['ar' => $title, 'en' => $title],
                'contents' => ['ar' => $message, 'en' => $message],
                'priority' => 10,
            ]);

            if ($response->successful()) {
                Log::info("OneSignal push sent to user {$userId}");
                return true;
            } else {
                Log::error("OneSignal push failed: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('OneSignal Error: ' . $e->getMessage());
            return false;
        }
    }
}
