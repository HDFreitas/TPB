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
        Schema::table('contatos', function (Blueprint $table) {
            // Campos que estão faltando baseados no modelo Contato
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('codigo')->nullable();
            $table->string('tipo_perfil')->nullable();
            $table->boolean('promotor')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Remover campos que não estão no modelo
            $table->dropColumn(['observacoes', 'status']);
            
            // Foreign keys
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('cliente_id');
            $table->index('codigo');
            $table->index('tipo_perfil');
            $table->index('promotor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contatos', function (Blueprint $table) {
            // Remover foreign keys
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            // Remover indexes
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['codigo']);
            $table->dropIndex(['tipo_perfil']);
            $table->dropIndex(['promotor']);
            
            // Remover campos adicionados
            $table->dropColumn([
                'cliente_id',
                'codigo',
                'tipo_perfil',
                'promotor',
                'created_by',
                'updated_by'
            ]);
            
            // Restaurar campos removidos
            $table->text('observacoes')->nullable();
            $table->boolean('status')->default(true);
        });
    }
};
