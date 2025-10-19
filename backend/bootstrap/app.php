<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\SetPermissionTeamMiddleware;
use App\Http\Middleware\DebugPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Registrar rotas do módulo Plataforma
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/plataforma.php'));
                
            // Registrar rotas do módulo CSI
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/csi.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt' => JwtAuthMiddleware::class,
            'admin' => CheckAdminRole::class,
            'set.permission.team' => SetPermissionTeamMiddleware::class,
            'api.permission' => \App\Http\Middleware\ApiPermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        
        // Add CORS middleware and set permission team
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            SetPermissionTeamMiddleware::class,
        ]);
        
        // Disable CSRF for API routes
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
