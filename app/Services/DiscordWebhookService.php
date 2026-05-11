<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DiscordWebhookService
{
    public static function send(array $data)
    {
        $url = env('DISCORD_WEBHOOK_URL');

        if (empty($url)) {
            // No webhook configured — nothing to do.
            return false;
        }

        try {
            $payload = [
                'content' => "[TicketApp] " . ($data['type'] ?? 'notification') . " - " . ($data['message'] ?? json_encode($data)),
            ];

            // Use a timeout to avoid hanging
            $response = Http::timeout(3)->post($url, $payload);

            return $response->successful();
        } catch (\Throwable $e) {
            // Logging could be added here, but avoid throwing to not break app flow
            return false;
        }
    }
}