<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('delivery_type', 10)->default('auto')->after('is_active');
        });

        DB::table('product_variants')->where('auto_delivery', false)->update(['delivery_type' => 'manual']);
        DB::table('product_variants')->where('auto_delivery', true)->update(['delivery_type' => 'auto', 'stock_quantity' => null]);

        DB::table('product_variants')
            ->whereNull('delivery_template')
            ->whereIn('product_id', DB::table('products')->whereNotNull('digital_instructions')->where('digital_instructions', '!=', '')->select('id'))
            ->orderBy('id')
            ->each(function ($variant) {
                $instructions = DB::table('products')->where('id', $variant->product_id)->value('digital_instructions');
                DB::table('product_variants')->where('id', $variant->id)->update(['delivery_template' => $instructions]);
            });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('auto_delivery');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_digital', 'digital_instructions']);
        });

        Schema::table('digital_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('digital_stock_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_digital')->default(true)->after('is_featured');
            $table->text('digital_instructions')->nullable()->after('is_digital');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('auto_delivery')->default(true)->after('is_active');
        });

        DB::table('product_variants')->where('delivery_type', 'manual')->update(['auto_delivery' => false]);

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('delivery_type');
        });
    }
};
