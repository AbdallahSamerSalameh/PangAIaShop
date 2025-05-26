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
        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('name', 100);
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('sku', 50)->unique();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('status', ['active', 'draft', 'discontinued'])->default('active');
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('dimensions', 50)->nullable();
            $table->text('warranty_info')->nullable();
            $table->text('return_policy')->nullable();

            $table->index('vendor_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->fullText(['name', 'description', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
