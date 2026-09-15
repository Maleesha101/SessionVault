<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_name');
            $table->string('device_type');
            $table->string('device_identifier');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_used_at');
            $table->boolean('is_current_device')->default(false);
            $table->boolean('is_trusted')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'device_identifier']);
            $table->index(['user_id', 'last_used_at']);
            $table->index(['user_id', 'is_current_device']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
