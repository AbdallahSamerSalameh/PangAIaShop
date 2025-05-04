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
        Schema::create('admins', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->id();
            $table->string('username', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);//->comment('Argon2 hash');
            $table->enum('role', ['Admin', 'Super Admin']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('avatar_url', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
            // $table->string('two_factor_secret', 255)->nullable();
            $table->timestamp('last_password_change')->nullable();
            $table->tinyInteger('failed_login_count')->default(0);
            $table->timestamp('last_login')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_verified')->default(false);
            $table->enum('two_factor_method', ['app', 'sms', 'email'])->default('app');
            $table->json('backup_codes')->nullable();
            $table->timestamp('two_factor_enabled_at')->nullable();
            $table->index('role');
        });

        // Create the trigger for 2FA validation
        DB::unprepared('
            CREATE TRIGGER validate_admin_2fa BEFORE INSERT ON admins FOR EACH ROW
            BEGIN
                IF NEW.two_factor_method = "sms" AND NEW.phone_number IS NULL THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Phone number required for SMS 2FA";
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS validate_admin_2fa');
        Schema::dropIfExists('admins');
    }
};