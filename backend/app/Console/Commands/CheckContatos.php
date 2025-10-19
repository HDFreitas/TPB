<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Csi\Models\Contato;

class CheckContatos extends Command
{
    protected $signature = 'contatos:check {clienteId}';
    protected $description = 'Check contatos for a specific cliente';

    public function handle()
    {
        $clienteId = $this->argument('clienteId');
        
        $contatos = Contato::where('cliente_id', $clienteId)->get();
        
        $this->info("Contatos encontrados para cliente ID {$clienteId}: " . $contatos->count());
        
        foreach ($contatos as $contato) {
            $this->info("ID: {$contato->id}, Nome: {$contato->nome}, Cliente ID: {$contato->cliente_id}");
        }
        
        // Verificar todos os contatos
        $allContatos = Contato::all();
        $this->info("\nTotal de contatos no banco: " . $allContatos->count());
        
        foreach ($allContatos as $contato) {
            $this->info("ID: {$contato->id}, Nome: {$contato->nome}, Cliente ID: {$contato->cliente_id}");
        }
    }
}
