<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            
            // Campos obrigatórios para isolamento multi-tenant e integrações
            $table->unsignedBigInteger('tenant_id');
            $table->string('razao_social');
            $table->string('cnpj_cpf')->unique();
            $table->string('codigo_senior')->unique()->nullable();
            $table->string('codigo_erp')->nullable();
            $table->boolean('status')->default(true);
            $table->string('nome_fantasia')->nullable();
            $table->string('codigo_ramo')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            
            // Campos de contato
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            
            // Campos de endereço
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            
            // Campo para observações
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['tenant_id', 'status']);
            $table->index('codigo_senior');
            
            // Foreign key para tenant
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};