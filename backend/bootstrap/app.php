<?php

use App\Http\Middleware\AttachRequestId;
use App\Support\ApiErrorResponder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'request.id' => AttachRequestId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            return ApiErrorResponder::make(
                'VALIDATION_ERROR',
                'Request validation failed.',
                422,
                ['fields' => $exception->errors()],
                (string) $request->attributes->get('request_id', 'unknown')
            );
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            return ApiErrorResponder::make(
                'RATE_LIMITED',
                'Too many requests. Please retry shortly.',
                429,
                null,
                (string) $request->attributes->get('request_id', 'unknown')
            );
        });
    })->create();
