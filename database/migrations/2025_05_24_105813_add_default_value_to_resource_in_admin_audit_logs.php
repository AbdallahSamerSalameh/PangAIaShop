<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            // Modify resource column to have a default value
            $table->string('resource')->default('system')->change();
            
            // Update any existing NULL values
            DB::statement("UPDATE admin_audit_logs SET resource = 'system' WHERE resource IS NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            // Remove the default value from the resource column
            $table->string('resource')->nullable()->change();
        });
    }
};
