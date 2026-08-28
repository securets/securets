<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('early_access_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('role')->default('developer');
            $table->string('team_size')->nullable();
            $table->text('use_case')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('early_access_subscribers');
    }
};
