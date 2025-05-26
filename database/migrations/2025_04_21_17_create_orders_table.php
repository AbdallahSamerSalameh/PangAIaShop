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
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('shipping_street', 255);
            $table->string('shipping_city', 100);
            $table->string('shipping_state', 100);
            $table->string('shipping_postal_code', 20);
            $table->char('shipping_country', 2);
            $table->string('billing_street', 255);
            $table->string('billing_city', 100);
            $table->string('billing_state', 100);
            $table->string('billing_postal_code', 20);
            $table->char('billing_country', 2);
            $table->decimal('total_amount', 10, 2);
            $table->timestamp('order_date')->useCurrent();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->foreignId('promo_code_id')->nullable()->constrained();
            $table->timestamp('expected_delivery_date')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('admins');

            $table->index('user_id');
            $table->index(['user_id', 'order_date']);
            $table->index('status');
            $table->index(['user_id', 'status', 'order_date']);
            $table->index('promo_code_id');
            $table->index('handled_by');
            $table->index(['order_date', 'status']);
            $table->index(['user_id', 'order_date', 'status', 'total_amount']);
            $table->index('order_date');
        });

        // Create trigger for amount validation
        DB::unprepared('
            CREATE TRIGGER before_order_amount_check BEFORE INSERT ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.total_amount < 0 OR NEW.discount_amount < 0 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Order amounts cannot be negative";
                END IF;
            END
        ');

        // Create trigger for promo code validation
        DB::unprepared('
            CREATE TRIGGER before_order_insert_promo BEFORE INSERT ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.promo_code_id IS NOT NULL THEN
                    IF (SELECT valid_until FROM promo_codes 
                        WHERE id = NEW.promo_code_id) < NOW() THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Promo code has expired";
                    END IF;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_order_amount_check');
        DB::unprepared('DROP TRIGGER IF EXISTS before_order_insert_promo');
        Schema::dropIfExists('orders');
    }
};
