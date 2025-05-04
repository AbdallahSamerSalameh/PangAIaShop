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
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('username', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255)->comment('Argon2 hash');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('avatar_url', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('two_factor_secret', 255)->nullable();
            $table->timestamp('last_password_change')->nullable();
            $table->tinyInteger('failed_login_count')->default(0);
            $table->enum('account_status', ['active', 'suspended', 'deactivated'])->default('active');
            $table->timestamp('last_login')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->binary('encrypted_recovery_email')->nullable();
            $table->boolean('two_factor_verified')->default(false);
            $table->enum('two_factor_method', ['app', 'sms', 'email'])->default('app');
            $table->json('backup_codes')->nullable();
            $table->timestamp('two_factor_enabled_at')->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();

            $table->index(['email', 'password_hash', 'account_status']);
        });

        // Create the trigger for 2FA validation
        DB::unprepared('
            CREATE TRIGGER validate_user_2fa BEFORE INSERT ON users FOR EACH ROW
            BEGIN
                IF NEW.two_factor_method = "sms" AND NEW.phone_number IS NULL THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Phone number required for SMS 2FA";
                END IF;
            END
        ');

        Schema::create('sessions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';            
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS validate_user_2fa');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
