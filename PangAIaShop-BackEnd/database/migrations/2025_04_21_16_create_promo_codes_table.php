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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->json('target_audience')->nullable();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins');
            $table->timestamp('created_at')->useCurrent();

            $table->index('is_active');
            $table->index('created_by');
            $table->index(['is_active', 'valid_until']);
            $table->index(['code', 'valid_until', 'is_active']);
        });

        // Create trigger for date validation
        DB::unprepared('
            CREATE TRIGGER before_promo_insert BEFORE INSERT ON promo_codes
            FOR EACH ROW
            BEGIN
                IF NEW.valid_from >= NEW.valid_until THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Valid from date must be before valid until date";
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_promo_insert');
        Schema::dropIfExists('promo_codes');
    }
};
