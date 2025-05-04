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
        Schema::create('shipments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->string('tracking_number', 255);
            $table->char('origin_country', 2)->nullable();
            $table->char('destination_country', 2)->nullable();
            $table->string('destination_region', 100)->nullable();
            $table->string('destination_zip', 20)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('shipping_zone', 100)->nullable();
            $table->enum('status', ['processing', 'shipped', 'delivered']);
            $table->decimal('actual_cost', 10, 2);
            $table->enum('shipping_method', ['standard', 'express', 'priority', 'economy', 'overnight'])->default('standard');
            $table->enum('service_level', ['standard', 'express'])->default('standard');
            $table->decimal('base_cost', 10, 2)->default(0.00);
            $table->decimal('per_item_cost', 8, 2)->default(0.00);
            $table->decimal('per_weight_unit_cost', 8, 2)->default(0.00);
            $table->integer('delivery_time_days')->nullable();
            $table->timestamp('shipped_at');
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins');
            $table->foreignId('updated_by')->nullable()->constrained('admins');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('order_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
