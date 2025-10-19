<?php

namespace App\Modules\Plataforma\Listeners;

use App\Modules\Plataforma\Events\TenantStatusChanged;
use App\Modules\Plataforma\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DeactivateUsersWhenTenantInactive implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param TenantStatusChanged $event
     * @return void
     */
    public function handle(TenantStatusChanged $event)
    {
        // Só processa se o status mudou de ativo para inativo
        if ($event->oldStatus === true && $event->newStatus === false) {
            
            Log::info("Desativando usuários do tenant {$event->tenant->id} - {$event->tenant->nome}");
            
            // Desativar todos os usuários ativos do tenant
            $affectedUsers = User::where('tenant_id', $event->tenant->id)
                ->where('status', true)
                ->update(['status' => false]);
            
            Log::info("Total de {$affectedUsers} usuários desativados do tenant {$event->tenant->id}");
        }
        
        // Se o tenant foi reativado, podemos opcionalmente reativar os usuários
        // (comentado por enquanto, pois pode não ser o comportamento desejado)
        /*
        if ($event->oldStatus === false && $event->newStatus === true) {
            Log::info("Tenant {$event->tenant->id} foi reativado - considere reativar usuários manualmente");
        }
        */
    }
}
