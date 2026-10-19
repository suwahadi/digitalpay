<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('original_price', 15, 2)->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_delivery')->default(true);
            $table->text('delivery_template')->nullable();
            $table->integer('max_downloads')->default(5);
            $table->integer('download_expiry_hours')->default(48);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id']);
            $table->index(['is_active', 'sort_order']);
            $table->index(['price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};