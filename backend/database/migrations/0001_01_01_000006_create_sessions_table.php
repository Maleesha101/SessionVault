<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload')->nullable();
                $table->timestamp('last_activity');
                $table->boolean('is_current')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'last_activity']);
                $table->index(['is_current', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
