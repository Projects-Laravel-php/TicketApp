<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DiscordWebhookService;

class ApiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('X-RateLimit-Remaining') == 0) {

            DiscordWebhookService::send([
                'type' => 'RATE_LIMIT',
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);
        }

        return $next($request);
    }
}