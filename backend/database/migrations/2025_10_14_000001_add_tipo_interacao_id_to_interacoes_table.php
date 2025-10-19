<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('interacoes', 'tipo_interacao_id')) {
                $table->unsignedBigInteger('tipo_interacao_id')->nullable()->after('cliente_id');
                // Index for queries
                $table->index(['tenant_id', 'tipo_interacao_id'], 'interacoes_tenant_tipo_interacao_index');

                // Foreign key constraint if the tipos_interacao table exists
                if (Schema::hasTable('tipos_interacao')) {
                    $table->foreign('tipo_interacao_id')
                        ->references('id')
                        ->on('tipos_interacao')
                        ->onDelete('set null');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('interacoes', function (Blueprint $table) {
            if (Schema::hasColumn('interacoes', 'tipo_interacao_id')) {
                // Drop FK if exists
                try {
                    $table->dropForeign(['tipo_interacao_id']);
                } catch (\Exception $e) {
                    // ignore if foreign key does not exist
                }

                // Drop index if exists
                try {
                    $table->dropIndex('interacoes_tenant_tipo_interacao_index');
                } catch (\Exception $e) {
                    // ignore
                }

                $table->dropColumn('tipo_interacao_id');
            }
        });
    }
};
