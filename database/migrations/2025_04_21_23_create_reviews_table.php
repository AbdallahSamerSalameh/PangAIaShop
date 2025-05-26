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
        Schema::create('reviews', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->tinyInteger('rating');
            $table->text('comment');
            $table->float('sentiment_score')->nullable();
            $table->integer('helpful_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('admins');
            $table->timestamp('moderated_at')->nullable();

            $table->index('product_id');
            $table->index('sentiment_score');
            $table->index('moderation_status');
            $table->index('user_id');
            $table->index('moderated_by');
            $table->index(['product_id', 'rating', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
