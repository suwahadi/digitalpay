<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('digital_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('digital_stock_id')->constrained()->onDelete('cascade');
            $table->string('download_token', 64)->unique();
            $table->text('delivery_content');
            $table->enum('delivery_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('delivery_trigger', ['auto', 'manual'])->default('auto');
            $table->text('manual_notes')->nullable();
            $table->timestamp('manually_triggered_at')->nullable();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('download_count')->default(0);
            $table->integer('max_downloads')->default(5);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('first_download_at')->nullable();
            $table->timestamp('last_download_at')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->json('delivery_metadata')->nullable();
            $table->timestamps();

            $table->index(['download_token']);
            $table->index(['delivery_status']);
            $table->index(['delivery_status', 'created_at']);
            $table->index(['expires_at']);
            $table->index(['delivery_trigger']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_deliveries');
    }
};