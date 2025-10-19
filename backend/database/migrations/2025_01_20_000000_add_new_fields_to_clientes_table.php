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
            // Novos campos solicitados
            $table->boolean('cliente_referencia')->default(false)->after('status');
            $table->enum('tipo_perfil', ['Relacional', 'Transacional'])->nullable()->after('cliente_referencia');
            $table->boolean('promotor')->default(false)->after('tipo_perfil');
            
            // Campos para auditoria
            $table->unsignedBigInteger('created_by')->nullable()->after('promotor');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Índices para performance
            $table->index('cliente_referencia');
            $table->index('tipo_perfil');
            $table->index('promotor');
            $table->index('created_by');
            $table->index('updated_by');
            
            // Foreign keys para auditoria
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['cliente_referencia']);
            $table->dropIndex(['tipo_perfil']);
            $table->dropIndex(['promotor']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropColumn(['cliente_referencia', 'tipo_perfil', 'promotor', 'created_by', 'updated_by']);
        });
    }
};
