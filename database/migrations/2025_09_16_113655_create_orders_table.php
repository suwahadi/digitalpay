<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled', 'expired'])->default('pending');
            $table->boolean('needs_manual_delivery')->default(false);
            $table->enum('delivery_method', ['auto', 'manual', 'mixed'])->nullable();
            $table->timestamp('manual_delivery_queued_at')->nullable();
            $table->timestamp('manual_processed_at')->nullable();
            $table->text('manual_notes')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('midtrans_order_id', 255)->nullable();
            $table->string('midtrans_transaction_id', 255)->nullable();
            $table->string('midtrans_snap_token', 255)->nullable();
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'cancelled', 'expired', 'failed'])->nullable();
            $table->timestamp('midtrans_paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_number']);
            $table->index(['customer_email']);
            $table->index(['customer_phone']);
            $table->index(['status']);
            $table->index(['needs_manual_delivery']);
            $table->index(['needs_manual_delivery', 'status']);
            $table->index(['midtrans_order_id']);
            $table->index(['midtrans_transaction_id']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};