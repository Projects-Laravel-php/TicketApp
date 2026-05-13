<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DiscordWebhookService;

class ApiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // If response is Too Many Requests, notify Discord
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() == 429) {
            try {
                DiscordWebhookService::send([
                    'type' => 'RATE_LIMIT',
                    'message' => 'Rate limit reached',
                    'endpoint' => $request->method() . ' ' . $request->path(),
                    'user' => $request->user()?->id ? $request->user()?->id . ' (' . $request->user()?->email . ')' : 'guest',
                    'ip' => $request->ip(),
                    'payload' => $request->except(['password', 'password_confirmation', 'current_password']),
                    'timestamp' => now()->toIso8601String(),
                    'attempts' => $request->header('X-RateLimit-Remaining')
                ]);
            } catch (\Throwable $e) {
                // avoid breaking request flow if Discord fails
            }
        }

        return $response;
    }
}