<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('order_id')->constrained()->onDelete('restrict');
            $table->foreignId('payment_method_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->enum('customer_type', ['guest', 'registered']);
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->string('gateway_provider', 100);
            $table->string('gateway_transaction_id', 255)->nullable();
            $table->string('gateway_reference', 255)->nullable();
            $table->json('gateway_response')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled', 'expired'])->default('pending');
            $table->string('payment_url', 1000)->nullable();
            $table->string('callback_url', 1000)->nullable();
            $table->string('return_url', 1000)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('midtrans_snap_token', 255)->nullable();
            $table->string('midtrans_transaction_status', 255)->nullable();
            $table->string('midtrans_fraud_status', 255)->nullable();
            $table->string('midtrans_payment_type', 255)->nullable();
            $table->decimal('midtrans_gross_amount', 15, 2)->nullable();
            $table->string('midtrans_signature_key', 255)->nullable();
            $table->json('midtrans_raw_response')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['payment_method_id']);
            $table->index(['customer_email']);
            $table->index(['gateway_transaction_id']);
            $table->index(['status']);
            $table->index(['midtrans_snap_token']);
            $table->index(['midtrans_transaction_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};