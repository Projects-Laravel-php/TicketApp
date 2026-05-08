<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DiscordWebhookService
{
    public static function send(array $data)
    {
        Http::post(env('DISCORD_WEBHOOK_URL'), [
            'content' => json_encode($data, JSON_PRETTY_PRINT)
        ]);
    }
}