<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_interacao', function (Blueprint $table) {
            if (!Schema::hasColumn('tipos_interacao', 'ultima_execucao')) {
                $table->timestamp('ultima_execucao')->nullable()->after('observacoes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipos_interacao', function (Blueprint $table) {
            if (Schema::hasColumn('tipos_interacao', 'ultima_execucao')) {
                $table->dropColumn('ultima_execucao');
            }
        });
    }
};