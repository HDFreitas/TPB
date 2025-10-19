<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Plataforma\Controllers\AuthController;
use App\Modules\Plataforma\Controllers\UsersController;
use App\Modules\Plataforma\Controllers\PerfisController;
use App\Modules\Plataforma\Controllers\PermissionController;
use App\Modules\Plataforma\Controllers\UserRoleController;
use App\Modules\Plataforma\Controllers\TenantsController;
use App\Http\Controllers\API\V1\LogsController;

/*
|--------------------------------------------------------------------------
| Rotas do Módulo Plataforma
|--------------------------------------------------------------------------
|
| Aqui estão as rotas relacionadas à autenticação, usuários, perfis,
| permissões e tenants da plataforma.
|
*/

Route::prefix('v1')->group(function () {
    // Rotas de Autenticação
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('jwt');
        Route::get('me', [AuthController::class, 'me'])->middleware('jwt');
        Route::get('user', [AuthController::class, 'me'])->middleware('jwt'); // Alias para compatibilidade
    });

    // Rotas protegidas
    Route::middleware('jwt')->group(function () {
        // Usuários
        Route::post('users/search', [UsersController::class, 'search']);
        Route::apiResource('users', UsersController::class);
        
        // Perfis
        Route::apiResource('perfis', PerfisController::class);
        
        // Rotas específicas para gerenciamento de perfis
        Route::prefix('perfis/{perfil}')->group(function () {
            Route::get('users', [PerfisController::class, 'getUsers']);
            Route::post('users', [PerfisController::class, 'associateUsers']);
            Route::delete('users/{user}', [PerfisController::class, 'removeUser']);
            Route::get('permissions', [PerfisController::class, 'getPermissions']);
            Route::post('permissions', [PerfisController::class, 'syncPermissions']);
        });
        
        // Permissões
        Route::apiResource('permissions', PermissionController::class);
        
        // User Roles
        Route::prefix('user-roles')->group(function () {
            Route::post('assign', [UserRoleController::class, 'assignRole']);
            Route::post('revoke', [UserRoleController::class, 'revokeRole']);
            Route::get('user/{user}', [UserRoleController::class, 'getUserRoles']);
        });
        
        // Tenants
        Route::apiResource('tenants', TenantsController::class);

        // Rotas básicas de logs
        Route::prefix('logs')->group(function () {
            // Lista todos os logs
            Route::get('/', [LogsController::class, 'index'])
                ->middleware('permission:logs.view');
            
            // Exibe um log específico
            Route::get('/{id}', [LogsController::class, 'show'])
                ->middleware('permission:logs.view');
            
            // Cria um novo log
            Route::post('/', [LogsController::class, 'store'])
                ->middleware('permission:logs.create');
            
            // Remove um log
            Route::delete('/{id}', [LogsController::class, 'destroy'])
                ->middleware('permission:logs.delete');
            
            // Busca logs com filtros
            Route::post('/search', [LogsController::class, 'search'])
                ->middleware('permission:logs.view');
        });
        
        // Logs
        Route::prefix('logs-dashboard')->group(function () {
            // Dashboard principal
            Route::get('/', [LogsController::class, 'dashboard'])
                ->middleware('permission:logs.dashboard');
            
            // Tendência por hora (últimas 24h)
            Route::get('/hourly-trend', [LogsController::class, 'hourlyTrend'])
                ->middleware('permission:logs.dashboard');
            
            // Logs por período
            Route::get('/period', [LogsController::class, 'logsByPeriod'])
                ->middleware('permission:logs.dashboard');
            
            // Logs de erro
            Route::get('/errors', [LogsController::class, 'errors'])
                ->middleware('permission:logs.view');
            
            // Logs por tipo
            Route::get('/type/{type}', [LogsController::class, 'byType'])
                ->middleware('permission:logs.view');
            
            // Lista de ações disponíveis
            Route::get('/actions', [LogsController::class, 'getActions'])
                ->middleware('permission:logs.view');
        });
    });
});
