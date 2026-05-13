<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Services\DiscordWebhookService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($e instanceof ThrottleRequestsException) {
                return;
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $type = $e instanceof HttpExceptionInterface && $status >= 500 ? 'CRITICAL_FAILURE' : 'EXCEPTION';
            $endpoint = app()->runningInConsole() ? 'console' : (request()?->method() . ' ' . request()?->path());
            $user = auth()->user()?->id ? auth()->user()?->id . ' (' . auth()->user()?->email . ')' : 'guest';
            $payload = [];

            if (!app()->runningInConsole() && request()?->all()) {
                $payload = request()->except(['password', 'password_confirmation', 'current_password']);
            }

            try {
                DiscordWebhookService::send([
                    'type' => $type,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'status' => $status,
                    'endpoint' => $endpoint,
                    'user' => $user,
                    'payload' => $payload,
                    'stacktrace' => $e->getTraceAsString(),
                    'timestamp' => now()->toIso8601String(),
                ]);

                if (!app()->runningInConsole() && request()?->attributes) {
                    request()->attributes->set('discord_exception_sent', true);
                }
            } catch (\Throwable $ex) {
                // Prevent cascading failures when Discord is down
            }

            if (app()->bound('sentry')) {
                try {
                    app('sentry')->captureException($e);
                    Log::info('Sentry event sent for exception', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'status' => $status,
                    ]);
                } catch (\Throwable $sent) {
                    Log::warning('Failed to send event to Sentry', [
                        'exception' => get_class($sent),
                        'message' => $sent->getMessage(),
                    ]);
                }
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => ['message' => $exception->getMessage() ?: 'No estás autenticado'],
            ], 401);
        }

        return parent::unauthenticated($request, $exception);
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

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => $e->getMessage() ?: 'No estás autorizado']
                ], 403);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => $e->getMessage() ?: 'Resource not found']
                ], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => $e->getMessage() ?: 'Error en la solicitud']
                ], $e->getStatusCode());
            }

            if ($e instanceof ThrottleRequestsException) {
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