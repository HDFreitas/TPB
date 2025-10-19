<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces do módulo Plataforma
use App\Modules\Plataforma\Interfaces\UserRepositoryInterface;
use App\Modules\Plataforma\Interfaces\PerfilRepositoryInterface;
use App\Modules\Plataforma\Interfaces\TenantRepositoryInterface;
use App\Modules\Plataforma\Interfaces\UserRoleRepositoryInterface;
use App\Modules\Plataforma\Interfaces\PermissionRepositoryInterface;

// Repositories do módulo Plataforma
use App\Modules\Plataforma\Repositories\UserRepository;
use App\Modules\Plataforma\Repositories\PerfilRepository;
use App\Modules\Plataforma\Repositories\TenantRepository;
use App\Modules\Plataforma\Repositories\UserRoleRepository;
use App\Modules\Plataforma\Repositories\PermissionRepository;

// Services do módulo Plataforma
use App\Modules\Plataforma\Services\TenantService;

// Interfaces do módulo CSI
use App\Modules\Csi\Interfaces\ClienteRepositoryInterface;
use App\Modules\Csi\Interfaces\InteracaoRepositoryInterface;
use App\Modules\Csi\Interfaces\ContatoRepositoryInterface;
use App\Modules\Csi\Interfaces\ConectorRepositoryInterface;

// Repositories do módulo CSI
use App\Modules\Csi\Repositories\ClienteRepository;
use App\Modules\Csi\Repositories\InteracaoRepository;
use App\Modules\Csi\Repositories\ContatoRepository;
use App\Modules\Csi\Repositories\ConectorRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bindings do módulo Plataforma
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository();
        });
        $this->app->bind(PerfilRepositoryInterface::class, function ($app) {
            return new PerfilRepository();
        });
        $this->app->bind(TenantRepositoryInterface::class, function ($app) {
            return new TenantRepository();
        });
        $this->app->bind(UserRoleRepositoryInterface::class, function ($app) {
            return new UserRoleRepository();
        });
        $this->app->bind(PermissionRepositoryInterface::class, function ($app) {
            return new PermissionRepository();
        });

        // Services do módulo Plataforma
        $this->app->bind(TenantService::class, function ($app) {
            return new TenantService(
                $app->make(TenantRepositoryInterface::class),
                $app->make(PerfilRepositoryInterface::class),
                $app->make(UserRepositoryInterface::class)
            );
        });

        // Bindings do módulo CSI
        $this->app->bind(ClienteRepositoryInterface::class, function ($app) {
            return new ClienteRepository();
        });
        $this->app->bind(InteracaoRepositoryInterface::class, function ($app) {
            return new InteracaoRepository();
        });
        $this->app->bind(ContatoRepositoryInterface::class, function ($app) {
            return new ContatoRepository();
        });
        $this->app->bind(ConectorRepositoryInterface::class, function ($app) {
            return new ConectorRepository();
        });
        
        // Bind do ConectorService
        $this->app->bind(\App\Modules\Csi\Services\ConectorService::class, function ($app) {
            return new \App\Modules\Csi\Services\ConectorService(
                $app->make(\App\Modules\Csi\Interfaces\ConectorRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
