<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DiscordWebhookService;

class DebugController extends Controller
{
    // Send test notifications to Discord and Sentry (only when APP_DEBUG=true)
    public function notifyTest(Request $request)
    {
        if (!config('app.debug')) {
            return response()->json(['success' => false, 'error' => ['message' => 'Not allowed']], 403);
        }

        $message = $request->input('message', 'Test notification from TicketApp');

        // Send to Discord (no-op if not configured)
        $discordOk = DiscordWebhookService::send([
            'type' => 'DEBUG_TEST',
            'message' => $message,
            'timestamp' => now()->toIso8601String()
        ]);

        // Send to Sentry if available
        $sentryOk = false;
        if (app()->bound('sentry')) {
            try {
                app('sentry')->captureMessage('Debug test: ' . $message);
                $sentryOk = true;
            } catch (\Throwable $e) {
                $sentryOk = false;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'discord' => $discordOk,
                'sentry' => $sentryOk
            ]
        ]);
    }
}
