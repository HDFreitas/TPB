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
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('cargo')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('status')->default(true);
            
            // Foreign key
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
            // Indexes
            $table->index('tenant_id');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contatos', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['email']);
            $table->dropColumn([
                'tenant_id',
                'nome',
                'email',
                'telefone',
                'cargo',
                'observacoes',
                'status'
            ]);
        });
    }
};
