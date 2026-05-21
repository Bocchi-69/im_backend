<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Handle unauthenticated users
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // If it's a RouteNotFoundException for 'login' on API routes, return 401
        if ($e instanceof RouteNotFoundException && $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // If it's an AuthenticationException, always return JSON for API
        if ($e instanceof AuthenticationException && $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return parent::render($request, $e);
    }
}