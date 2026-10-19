<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('image', 500)->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->text('digital_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['slug']);
            $table->index(['category_id']);
            $table->index(['is_active', 'is_popular']);
            $table->index(['is_active', 'is_featured']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};