<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('type', ['qris', 'ewallet', 'virtual_account', 'bank_transfer']);
            $table->string('provider', 100);
            $table->string('icon', 500)->nullable();
            $table->enum('fee_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['code']);
            $table->index(['type']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};