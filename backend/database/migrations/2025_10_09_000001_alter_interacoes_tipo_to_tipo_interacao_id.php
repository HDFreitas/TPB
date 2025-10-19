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
        Schema::table('interacoes', function (Blueprint $table) {
            // Adiciona campo tipo_interacao_id
            $table->unsignedBigInteger('tipo_interacao_id')->nullable()->after('cliente_id');
            $table->foreign('tipo_interacao_id')->references('id')->on('tipos_interacao')->onDelete('set null');
            // Remove campo antigo tipo
            $table->dropColumn('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interacoes', function (Blueprint $table) {
            // Adiciona campo tipo novamente
            $table->string('tipo')->nullable()->after('cliente_id');
            // Remove FK e campo tipo_interacao_id
            $table->dropForeign(['tipo_interacao_id']);
            $table->dropColumn('tipo_interacao_id');
        });
    }
};
