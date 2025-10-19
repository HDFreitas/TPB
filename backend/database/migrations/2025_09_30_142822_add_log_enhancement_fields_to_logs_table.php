<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::table('logs', function (Blueprint $table) {
            if (!Schema::hasColumn('logs', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('user_id');
                $table->index('tenant_id');
                $table->index(['tenant_id', 'created_at']);
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }
            
            $table->enum('log_type', ['info', 'error', 'warning', 'debug'])->default('info');
            
            $table->enum('status', ['active', 'inactive', 'processed'])->default('active');
            
            $table->text('content')->nullable();
            
            $table->index(['log_type', 'created_at']);
            $table->index(['status', 'created_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::table('logs', function (Blueprint $table) {

            $table->dropForeign(['tenant_id']);
            
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropIndex(['log_type', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            
            $table->dropColumn(['tenant_id', 'log_type', 'status', 'content']);
    });
}
};
