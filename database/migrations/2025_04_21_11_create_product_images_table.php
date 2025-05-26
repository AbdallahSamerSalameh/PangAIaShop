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
        Schema::create('product_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('image_url', 255);
            $table->string('alt_text', 100);
            $table->enum('image_type', ['thumbnail', 'gallery', '360-view']);
            $table->boolean('is_primary')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('admins');
            $table->timestamp('uploaded_at')->useCurrent();

            $table->index('product_id');
            $table->index('uploaded_by');
        });

        // Create trigger for primary image management
        DB::unprepared('
            CREATE TRIGGER before_product_image_insert BEFORE INSERT ON product_images
            FOR EACH ROW
            BEGIN
                IF NEW.is_primary = 1 THEN
                    UPDATE product_images SET is_primary = 0 WHERE product_id = NEW.product_id;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_product_image_insert');
        Schema::dropIfExists('product_images');
    }
};
