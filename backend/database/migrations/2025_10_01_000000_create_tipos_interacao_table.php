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
        Schema::create('tipos_interacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('nome');
            $table->foreignId('conector_id')->nullable()->constrained('conectores')->onDelete('set null');
            $table->integer('porta')->nullable()->comment('Porta para conector ERP');
            $table->string('rota')->nullable()->comment('Rota para conector Movidesk');
            $table->boolean('status')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['tenant_id', 'nome']);
            $table->index(['tenant_id', 'status']);
            $table->index('conector_id');
            
            // Constraint de unicidade por tenant
            $table->unique(['tenant_id', 'nome'], 'tipos_interacao_tenant_nome_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_interacao');
    }
};
