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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->foreignId('product_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->boolean('is_primary_category')->default(false);
            $table->foreignId('added_by')->nullable()->constrained('admins');
            $table->timestamp('added_at')->useCurrent();

            $table->primary(['product_id', 'category_id']);
            $table->index('category_id');
            $table->index('added_by');
        });

        // Create trigger for primary category management
        DB::unprepared('
            CREATE TRIGGER before_product_category_insert BEFORE INSERT ON product_categories
            FOR EACH ROW
            BEGIN
                IF NEW.is_primary_category = 1 THEN
                    UPDATE product_categories 
                    SET is_primary_category = 0 
                    WHERE product_id = NEW.product_id;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_product_category_insert');
        Schema::dropIfExists('product_categories');
    }
};
