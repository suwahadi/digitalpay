<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guest_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('first_visit_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrentOnUpdate();
            $table->foreignId('merged_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('merged_at')->nullable();

            $table->index(['session_id']);
            $table->index(['email']);
            $table->index(['phone']);
            $table->index(['merged_to_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_sessions');
    }
};