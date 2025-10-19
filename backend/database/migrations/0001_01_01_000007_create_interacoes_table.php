<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('cliente_id');
            $table->string('tipo');
            $table->string('origem');
            $table->text('descricao')->nullable();
            $table->dateTime('data_interacao');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Índices para performance
            $table->index(['tenant_id', 'cliente_id']);
            $table->index(['tenant_id', 'tipo']);
            $table->index(['tenant_id', 'data_interacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interacoes');
    }
};