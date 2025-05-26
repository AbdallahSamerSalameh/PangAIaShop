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
        if (Schema::hasTable('carts') && !Schema::hasColumn('carts', 'status')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->enum('status', ['active', 'checkout', 'completed', 'abandoned'])->default('active')->after('carts_expiry');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'status')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
