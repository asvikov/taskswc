<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\ApiAuthenticationHandler;
use App\Exceptions\ApiNotFoundHttpHandler;
use App\Exceptions\ApiValidationHandler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(new ApiAuthenticationHandler());
        $exceptions->renderable(new ApiNotFoundHttpHandler());
        $exceptions->renderable(new ApiValidationHandler());
    })->create();
