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
        // Check if the table already exists
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_general_ci';
                $table->id();
                $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->index('cart_id');
                $table->index('product_id');
            });
        }
        
        // Add promo_code and discount columns to carts table if they don't exist
        if (Schema::hasTable('carts') && !Schema::hasColumn('carts', 'promo_code')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('promo_code')->nullable()->after('carts_expiry');
                $table->decimal('discount', 10, 2)->default(0)->after('promo_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'promo_code')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn(['promo_code', 'discount']);
            });
        }
    }
};
