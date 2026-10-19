<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_session_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('orders_migrated_count')->default(0);
            $table->enum('migration_method', ['registration', 'login', 'manual']);
            $table->foreignId('migrated_by_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['guest_session_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_migrations');
    }
};