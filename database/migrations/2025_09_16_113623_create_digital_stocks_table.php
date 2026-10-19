<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('digital_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->string('stock_name');
            $table->json('stock_data');
            $table->enum('status', ['available', 'reserved', 'delivered', 'expired'])->default('available');
            $table->timestamp('added_at')->useCurrent();
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->unsignedBigInteger('assigned_to_order_item_id')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['product_variant_id', 'status']);
            $table->index(['status', 'added_at']);
            $table->index(['status']);
            $table->index(['assigned_to_order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_stocks');
    }
};