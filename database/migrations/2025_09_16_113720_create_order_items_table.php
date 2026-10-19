<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->constrained()->onDelete('restrict');
            $table->string('product_name');
            $table->string('variant_name');
            $table->decimal('price', 15, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 15, 2);
            $table->enum('delivery_status', ['pending', 'delivered', 'failed'])->default('pending');
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['product_id']);
            $table->index(['product_variant_id']);
            $table->index(['delivery_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};