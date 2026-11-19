<?php

use App\Http\Middleware\EnsureAdmin;
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
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        apiPrefix: 'api/v1',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['admin' => EnsureAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Semua error di /api/* selalu JSON
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'code'    => 'VALIDATION_ERROR',
                'errors'  => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'Silakan login terlebih dahulu.',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        });

        // Model tidak ditemukan (route model binding) dan URL tidak ada
        $notFound = fn () => response()->json([
            'message' => 'Data tidak ditemukan.',
            'code'    => 'NOT_FOUND',
        ], 404);
        $exceptions->render(fn (ModelNotFoundException $e) => $notFound());
        $exceptions->render(fn (NotFoundHttpException $e) => $notFound());
    })->create();
