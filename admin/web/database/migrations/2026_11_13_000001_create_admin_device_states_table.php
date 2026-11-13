<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_device_states', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id')->unique();
            $table->string('status')->default('reported_lost');
            $table->string('acted_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_device_states');
    }
};
