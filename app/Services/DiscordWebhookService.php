<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    public static function send(array $data)
    {
        $url = env('DISCORD_WEBHOOK_URL');

        Log::info('Discord webhook attempt', ['type' => $data['type'] ?? 'unknown', 'url_set' => !empty($url)]);

        if (empty($url)) {
            Log::warning('Discord webhook URL not configured');
            return false;
        }

        try {
            $type = strtoupper($data['type'] ?? 'NOTIFICATION');
            $rawMessage = $data['message'] ?? 'No message provided';
            $cleanMessage = strip_tags($rawMessage);

            if (str_contains(strtolower($cleanMessage), '<!doctype') || str_contains(strtolower($cleanMessage), '<html') || str_contains(strtolower($cleanMessage), 'body{')) {
                $cleanMessage = $type === 'CRITICAL_FAILURE'
                    ? 'HTTP 500 Internal Server Error'
                    : 'Server error detected';
            }

            $message = Str::limit($cleanMessage, 200);

            $fields = [];
            $addField = function ($name, $value) use (&$fields) {
                if (blank($value)) {
                    return;
                }

                $fields[] = [
                    'name' => $name,
                    'value' => is_string($value)
                        ? Str::limit($value, 1024)
                        : Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 1024),
                    'inline' => false,
                ];
            };

            $addField('Severity', $type);
            $addField('Status', $data['status'] ?? null);
            $addField('Endpoint', $data['endpoint'] ?? null);
            $addField('User', $data['user'] ?? null);
            $addField('Exception', $data['exception'] ?? null);
            $addField('Payload', $data['payload'] ?? null);
            $addField('Stacktrace', $data['stacktrace'] ?? null);

            $templates = [
                'CRITICAL_FAILURE' => [
                    'title' => 'Critical failure detected',
                    'description' => 'Fallo crítico capturado. Revisa el endpoint y la causa principal.',
                    'color' => 16711680,
                ],
                'EXCEPTION' => [
                    'title' => 'Exception captured',
                    'description' => 'Se capturó una excepción en la aplicación.',
                    'color' => 16753920,
                ],
                'RATE_LIMIT' => [
                    'title' => 'Rate limit alert',
                    'description' => 'El límite de peticiones fue alcanzado en una ruta protegida.',
                    'color' => 16776960,
                ],
                'DEBUG_TEST' => [
                    'title' => 'Debug test notification',
                    'description' => 'Prueba manual de notificación a Discord desde TicketApp.',
                    'color' => 3447003,
                ],
                'DEFAULT' => [
                    'title' => 'Application notification',
                    'description' => 'Evento enviado desde TicketApp.',
                    'color' => 3447003,
                ],
            ];

            $template = $templates[$type] ?? $templates['DEFAULT'];

            $payload = [
                'content' => "[TicketApp] [{$type}] " . ($data['endpoint'] ?? 'No endpoint defined'),
                'embeds' => [
                    [
                        'title' => $data['title'] ?? $template['title'],
                        'description' => $data['detail'] ?? $message ?: $template['description'],
                        'fields' => $fields,
                        'footer' => [
                            'text' => 'TicketApp alert',
                        ],
                        'timestamp' => now()->toIso8601String(),
                        'color' => $template['color'],
                    ],
                ],
            ];

            if (empty($fields)) {
                unset($payload['embeds'][0]['fields']);
            }

            $response = Http::timeout(3)
                ->withBody(json_encode($payload), 'application/json')
                ->post($url);

            $success = $response->successful();
            Log::info('Discord webhook response', [
                'success' => $success,
                'status' => $response->status(),
                'body' => $response->body(),
                'message' => $message,
            ]);

            if (!$success) {
                Log::error('Discord webhook rejected payload', ['payload' => $payload]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::error('Discord webhook failed', ['exception' => get_class($e), 'message' => $e->getMessage()]);
            return false;
        }
    }
}
