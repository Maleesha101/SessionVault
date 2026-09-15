<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('session_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('event_timestamp');
            $table->timestamps();
            
            $table->index(['event_type', 'event_timestamp']);
            $table->index(['user_id', 'event_timestamp']);
            $table->index(['session_id', 'event_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
