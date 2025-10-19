<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_interacao', function (Blueprint $table) {
            if (Schema::hasColumn('tipos_interacao', 'porta')) {
                $table->string('porta', 32)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipos_interacao', function (Blueprint $table) {
            if (Schema::hasColumn('tipos_interacao', 'porta')) {
                $table->integer('porta')->nullable()->change();
            }
        });
    }
};
