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
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('wishlist_id')->constrained()->cascadeOnDelete()->comment('FK to wishlists table');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete()->comment('FK to products table');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete()->comment('FK to product_variants table (optional)');
            $table->timestamp('added_at')->useCurrent()->comment('Timestamp when item was added');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium')->comment('Priority level of the wishlist item');
            $table->text('notes')->nullable()->comment('Optional user notes about the item');
            $table->softDeletes(); // Added for SoftDeletes support

            $table->unique(['wishlist_id', 'product_id', 'variant_id'], 'uk_wishlist_product_variant');
            $table->index('wishlist_id');
            $table->index('product_id');
            $table->index('variant_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
