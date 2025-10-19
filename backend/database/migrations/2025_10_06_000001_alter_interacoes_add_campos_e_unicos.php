<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('interacoes', 'chave')) {
                $table->string('chave')->nullable()->after('cliente_id');
            }
            if (!Schema::hasColumn('interacoes', 'titulo')) {
                $table->string('titulo')->nullable()->after('data_interacao');
            }
            if (!Schema::hasColumn('interacoes', 'valor')) {
                $table->decimal('valor', 15, 2)->nullable()->after('titulo');
            }

            // Unique composto: (tenant_id, origem, chave)
            $table->unique(['tenant_id', 'origem', 'chave'], 'interacoes_tenant_origem_chave_unique');
        });
    }

    public function down(): void
    {
        Schema::table('interacoes', function (Blueprint $table) {
            $table->dropUnique('interacoes_tenant_origem_chave_unique');
            if (Schema::hasColumn('interacoes', 'valor')) {
                $table->dropColumn('valor');
            }
            if (Schema::hasColumn('interacoes', 'titulo')) {
                $table->dropColumn('titulo');
            }
            if (Schema::hasColumn('interacoes', 'chave')) {
                $table->dropColumn('chave');
            }
        });
    }
};


