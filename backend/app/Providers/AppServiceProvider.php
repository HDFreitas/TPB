<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\Plataforma\Interfaces\TenantRepositoryInterface;
use App\Modules\Plataforma\Repositories\TenantRepository;
use App\Interfaces\LogRepositoryInterface;
use App\Repositories\LogRepository;
use App\Modules\Plataforma\Interfaces\PerfilRepositoryInterface;
use App\Modules\Plataforma\Repositories\PerfilRepository;
use App\Modules\Csi\Interfaces\InteracaoRepositoryInterface;
use App\Modules\Csi\Repositories\InteracaoRepository;
use App\Modules\Plataforma\Interfaces\UserRoleRepositoryInterface;
use App\Modules\Plataforma\Repositories\UserRoleRepository;
use App\Modules\Csi\Interfaces\ConectorRepositoryInterface;
use App\Modules\Csi\Repositories\ConectorRepository;
use App\Modules\Csi\Interfaces\TipoInteracaoRepositoryInterface;
use App\Modules\Csi\Repositories\TipoInteracaoRepository;
use App\Modules\Plataforma\Events\TenantStatusChanged;
use App\Modules\Plataforma\Listeners\DeactivateUsersWhenTenantInactive;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
        $this->app->bind(PerfilRepositoryInterface::class, PerfilRepository::class);
        $this->app->bind(InteracaoRepositoryInterface::class, InteracaoRepository::class);
        $this->app->bind(UserRoleRepositoryInterface::class, UserRoleRepository::class);
        $this->app->bind(ConectorRepositoryInterface::class, ConectorRepository::class);
        $this->app->bind(TipoInteracaoRepositoryInterface::class, TipoInteracaoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar eventos e listeners
        Event::listen(
            TenantStatusChanged::class,
            DeactivateUsersWhenTenantInactive::class
        );
    }
}
