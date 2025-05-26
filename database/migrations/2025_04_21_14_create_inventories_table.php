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
        Schema::create('inventories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants');
            $table->integer('quantity');
            $table->integer('reserved_quantity')->default(0);
            $table->string('location', 255);
            $table->timestamp('last_restocked')->nullable();
            $table->integer('low_stock_threshold')->default(10);
            $table->foreignId('updated_by')->nullable()->constrained('admins');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('product_id');
            $table->index('variant_id');
            $table->index('updated_by');
            $table->index(['product_id', 'quantity', 'reserved_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
