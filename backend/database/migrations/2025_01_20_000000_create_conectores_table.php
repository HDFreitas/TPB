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
        Schema::create('conectores', function (Blueprint $table) {
            $table->id();
            
            // Campos obrigatórios para isolamento multi-tenant
            $table->unsignedBigInteger('tenant_id');
            $table->string('codigo'); // 1-ERP, 2-Movidesk, 3-CRM Eleve
            $table->string('nome'); // Nome descritivo do conector
            $table->string('url')->nullable(); // URL base para conexão
            $table->boolean('status')->default(true);
            
            // Campos específicos para diferentes tipos de conector
            $table->string('usuario')->nullable(); // Para conector 1-ERP
            $table->string('senha')->nullable(); // Para conector 1-ERP
            $table->text('token')->nullable(); // Para conector 2-Movidesk
            
            // Campos adicionais para configuração
            $table->text('configuracao_adicional')->nullable(); // JSON para configurações específicas
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['tenant_id', 'status']);
            $table->index('codigo');
            
            // Índice único composto: código deve ser único por tenant
            $table->unique(['tenant_id', 'codigo']);
            
            // Foreign key para tenant
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conectores');
    }
};

