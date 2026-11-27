<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time sign-in codes for the admin web console. Only a keyed hash of
 * each code is stored; the plain code exists only in the email.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_login_otps')) {
            return;
        }

        Schema::create('admin_login_otps', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('code_hash', 64);
            $table->timestamp('expires_at');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('consumed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_otps');
    }
};
