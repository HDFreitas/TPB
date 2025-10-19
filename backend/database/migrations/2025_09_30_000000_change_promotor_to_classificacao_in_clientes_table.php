<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Remove o campo promotor (boolean) e adiciona classificacao (enum)
            $table->dropIndex(['promotor']); // Remove o índice primeiro
            $table->dropColumn('promotor');
            
            // Adiciona o novo campo classificacao
            $table->enum('classificacao', ['Promotor', 'Neutro', 'Detrator'])->nullable()->after('tipo_perfil');
            
            // Adiciona índice para o novo campo
            $table->index('classificacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Remove o campo classificacao e volta o promotor
            $table->dropIndex(['classificacao']);
            $table->dropColumn('classificacao');
            
            // Volta o campo promotor
            $table->boolean('promotor')->default(false)->after('tipo_perfil');
            $table->index('promotor');
        });
    }
};
