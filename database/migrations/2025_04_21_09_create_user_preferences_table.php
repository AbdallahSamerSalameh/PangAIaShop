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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->foreignId('user_id')->constrained()->unique();
            $table->char('language', 2)->default('en');
            $table->char('currency', 3)->default('USD');
            $table->enum('theme_preference', ['light', 'dark', 'system'])->default('light');
            $table->json('notification_preferences')->nullable()->comment('JSON object with notification preferences');
            $table->boolean('ai_interaction_enabled')->default(true);
            $table->boolean('chat_history_enabled')->default(true);
            $table->timestamp('last_interaction_date')->nullable();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
