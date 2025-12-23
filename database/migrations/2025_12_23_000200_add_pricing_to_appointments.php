<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Pricing fields
            $table->decimal('subtotal_amount', 10, 2)->default(0)->after('status');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal_amount');
            $table->decimal('total_amount', 10, 2)->default(0)->after('discount_amount');

            // Coupon relationship
            $table->foreignId('coupon_id')->nullable()->after('total_amount')
                ->constrained('coupons')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'subtotal_amount', 'discount_amount', 'total_amount']);
        });
    }
};
