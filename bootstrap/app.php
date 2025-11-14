<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleBasedAccess::class,
            'organization.access' => \App\Http\Middleware\OrganizationAccess::class,
        ]);
    })
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions): void {
        // Report all exceptions to the logging system
        $exceptions->report(function (\Throwable $e) {
            // Log all exceptions with context
            Log::error('Application Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
        
        // Optionally, stop reporting specific exceptions
        // $exceptions->dontReport([
        //     \Illuminate\Auth\AuthenticationException::class,
        //     \Illuminate\Auth\Access\AuthorizationException::class,
        // ]);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->create();
