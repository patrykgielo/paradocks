<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ensure invoice_nip column is exactly 10 characters (stores digits only, no dashes)
     * The Appointment model's invoiceNip() mutator strips dashes before storage.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Modify column to ensure it's exactly 10 characters
            // NIP format: 10 digits without dashes (e.g., 8222370339)
            $table->string('invoice_nip', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - column definition already correct
        // This migration is defensive to ensure consistency
    }
};
