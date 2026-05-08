<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Services\DiscordWebhookService;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {

            DiscordWebhookService::send([
                'type' => 'SERVER_ERROR',
                'message' => $e->getMessage(),
                'timestamp' => now()
            ]);

            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }
}