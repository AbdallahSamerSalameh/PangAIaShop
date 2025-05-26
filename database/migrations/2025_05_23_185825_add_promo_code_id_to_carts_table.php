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
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'promo_code')) {
                $table->string('promo_code')->nullable();
            }
            
            if (!Schema::hasColumn('carts', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('carts', 'promo_code_id')) {
                $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'promo_code_id')) {
                $table->dropConstrainedForeignId('promo_code_id');
            }
            
            if (Schema::hasColumn('carts', 'promo_code')) {
                $table->dropColumn('promo_code');
            }
            
            if (Schema::hasColumn('carts', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};
