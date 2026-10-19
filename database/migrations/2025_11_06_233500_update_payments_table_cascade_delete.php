<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['order_id']);
            
            // Recreate the foreign key with cascade delete
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the cascade foreign key
            $table->dropForeign(['order_id']);
            
            // Restore the original restrict foreign key
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('restrict');
        });
    }
};
