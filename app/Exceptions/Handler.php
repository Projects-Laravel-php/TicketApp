<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Services\DiscordWebhookService;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            try {
                DiscordWebhookService::send([
                    'type' => 'SERVER_ERROR',
                    'message' => $e->getMessage(),
                    'timestamp' => now()
                ]);
            } catch (\Throwable $ex) {
                // Prevent cascading failures when Discord is down
            }

            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    public function render($request, Throwable $e)
    {
        // If request expects JSON or is under /api, return structured JSON errors
        if ($request->wantsJson() || $request->is('api/*')) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Validation failed',
                        'errors' => $e->errors()
                    ]
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => $e->getMessage() ?: 'Unauthenticated']
                ], 401);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => 'Resource not found']
                ], 404);
            }

            if ($e instanceof ThrottleRequestsException) {
                try {
                    DiscordWebhookService::send([
                        'type' => 'RATE_LIMIT',
                        'endpoint' => $request->path(),
                        'ip' => $request->ip(),
                        'timestamp' => now()
                    ]);
                } catch (\Throwable $ex) {
                }

                return response()->json([
                    'success' => false,
                    'error' => ['message' => $e->getMessage()]
                ], 429);
            }

            // Generic server error
            return response()->json([
                'success' => false,
                'error' => ['message' => $e->getMessage()]
            ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        }

        return parent::render($request, $e);
    }
}