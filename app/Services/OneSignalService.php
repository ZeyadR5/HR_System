<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    public static function sendToUser($userId, string $title, string $message): bool
    {
        $appId = config('services.onesignal.app_id', env('ONESIGNAL_APP_ID', '9d306f1d-5e6d-4d06-91ed-0395ed04a46a'));
        $apiKey = config('services.onesignal.rest_api_key', env('ONESIGNAL_REST_API_KEY'));

        if (empty($appId) || empty($apiKey)) {
            Log::warning('OneSignal skipped: missing APP_ID or REST_API_KEY');
            return false;
        }

        $user = \App\Models\User::find($userId);

        $payload = [
            'app_id' => $appId,
            'target_channel' => 'push',
            'headings' => ['ar' => $title, 'en' => $title],
            'contents' => ['ar' => $message, 'en' => $message],
            'priority' => 10,
        ];

        if ($user && !empty($user->onesignal_id)) {
            $payload['include_player_ids'] = [$user->onesignal_id];
        } else {
            $payload['include_aliases'] = [
                'external_id' => [(string) $userId]
            ];
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Key ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(6)->post('https://api.onesignal.com/notifications', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['errors'])) {
                    Log::warning("OneSignal push warning for user {$userId}: " . json_encode($data['errors'], JSON_UNESCAPED_UNICODE));
                } else {
                    Log::info("OneSignal push successfully dispatched to user {$userId}. ID: " . ($data['id'] ?? 'N/A'));
                }
                return true;
            } else {
                Log::error("OneSignal push HTTP {$response->status()} failed: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('OneSignal Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendToUsers(array $userIds, string $title, string $message): bool
    {
        $appId = config('services.onesignal.app_id', env('ONESIGNAL_APP_ID', '9d306f1d-5e6d-4d06-91ed-0395ed04a46a'));
        $apiKey = config('services.onesignal.rest_api_key', env('ONESIGNAL_REST_API_KEY'));

        if (empty($appId) || empty($apiKey) || empty($userIds)) {
            return false;
        }

        $users = \App\Models\User::whereIn('id', $userIds)->get();
        $playerIds = $users->pluck('onesignal_id')->filter()->unique()->values()->all();
        $externalIds = array_map('strval', $userIds);

        $payload = [
            'app_id' => $appId,
            'target_channel' => 'push',
            'headings' => ['ar' => $title, 'en' => $title],
            'contents' => ['ar' => $message, 'en' => $message],
            'priority' => 10,
        ];

        if (!empty($playerIds)) {
            $payload['include_player_ids'] = $playerIds;
        }
        if (!empty($externalIds)) {
            $payload['include_aliases'] = ['external_id' => $externalIds];
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Key ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(6)->post('https://api.onesignal.com/notifications', $payload);

            if ($response->successful()) {
                Log::info("OneSignal push dispatched to management users (" . count($userIds) . " users)");
                return true;
            } else {
                Log::error("OneSignal push failed for management: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('OneSignal Management Error: ' . $e->getMessage());
            return false;
        }
    }
}
