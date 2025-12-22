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
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('coupon_id')
                ->constrained('coupons')
                ->cascadeOnDelete();

            $table->foreignId('appointment_id')
                ->constrained('appointments')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Usage details
            $table->decimal('discount_amount', 10, 2)
                ->comment('Actual discount amount applied to this appointment');

            $table->timestamp('used_at')
                ->comment('When the coupon was used');

            $table->timestamps();

            // Indexes for reporting
            $table->index('coupon_id');
            $table->index('customer_id');
            $table->index('used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
