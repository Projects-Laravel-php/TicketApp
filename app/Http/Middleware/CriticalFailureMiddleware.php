<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\DiscordWebhookService;
use Illuminate\Support\Facades\Log;

class CriticalFailureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Send Discord alert for critical failures (HTTP 500+)
        if ($response->getStatusCode() >= 500 && !$request->attributes->get('discord_exception_sent')) {
            Log::info('Critical failure detected', [
                'status' => $response->getStatusCode(),
                'endpoint' => $request->method() . ' ' . $request->path(),
            ]);

            try {
                $user = auth()->user()?->id ? auth()->user()?->id . ' (' . auth()->user()?->email . ')' : 'guest';
                $payload = $request->except(['password', 'password_confirmation', 'current_password']);

                $result = DiscordWebhookService::send([
                    'type' => 'CRITICAL_FAILURE',
                    'message' => 'HTTP ' . $response->getStatusCode() . ' - ' . $response->getContent(),
                    'status' => $response->getStatusCode(),
                    'endpoint' => $request->method() . ' ' . $request->path(),
                    'user' => $user,
                    'payload' => $payload,
                    'timestamp' => now()->toIso8601String(),
                ]);

                Log::info('Discord alert sent', ['result' => $result]);
            } catch (\Throwable $e) {
                Log::error('Failed to send Discord alert', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }
}
