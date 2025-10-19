<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UpdateRoleGuards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:update-guards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza todas as roles para usar guard api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Atualizando guards das roles para api...');

        // Atualizar todas as roles para usar guard 'api'
        $updated = DB::table('roles')
            ->where('guard_name', 'web')
            ->update(['guard_name' => 'api']);

        $this->info("✅ {$updated} roles atualizadas para guard 'api'");

        // Verificar se há roles com guard 'api'
        $apiRoles = Role::where('guard_name', 'api')->count();
        $this->info("📊 Total de roles com guard 'api': {$apiRoles}");

        $this->info('🎉 Guards das roles atualizados com sucesso!');
        return 0;
    }
}