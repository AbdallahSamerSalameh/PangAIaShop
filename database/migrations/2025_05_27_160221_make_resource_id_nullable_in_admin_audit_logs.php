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
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            // Make resource_id nullable to allow system-level actions without associated models
            $table->integer('resource_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            // Revert resource_id back to non-nullable
            $table->integer('resource_id')->nullable(false)->change();
        });
    }
};
