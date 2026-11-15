<?php

use App\Modules\History\Exceptions\InvalidHistoryPayloadException;
use App\Modules\History\Exceptions\UnsupportedModuleException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // En /api/* un guest debe recibir 401, no una redirección a login.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('api/*') ? null : '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Todas las respuestas de /api/* son JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // AuthenticationException → 401 JSON.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'No autenticado.',
                ], 401);
            }
        });

        // ValidationException → 422 con errores por campo.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Payload de historial inválido → 422 con el mismo formato.
        $exceptions->render(function (InvalidHistoryPayloadException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Módulo desconocido → 422.
        $exceptions->render(function (UnsupportedModuleException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        // Modelo inexistente → 404.
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Recurso no encontrado.',
                ], 404);
            }
        });

        // Ruta inexistente → 404.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Endpoint no encontrado.',
                ], 404);
            }
        });

        // Cualquier otro error → 500 JSON fuera de debug.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && ! config('app.debug')) {
                $payload = [
                    'message' => 'Error interno del servidor.',
                ];

                // Los 4xx conservan su mensaje original.
                if (method_exists($e, 'getStatusCode')) {
                    $status = $e->getStatusCode();
                    if ($status >= 400 && $status < 500) {
                        $payload['message'] = $e->getMessage() ?: $payload['message'];
                        return response()->json($payload, $status);
                    }
                }

                return response()->json($payload, 500);
            }
        });
    })->create();