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
            // Invoice request flag
            $table->boolean('invoice_requested')->default(false)->after('notify_sms');

            // Invoice type
            $table->enum('invoice_type', ['individual', 'company', 'foreign_eu', 'foreign_non_eu'])
                ->nullable()
                ->after('invoice_requested');

            // Company details
            $table->string('invoice_company_name')->nullable()->after('invoice_type');
            $table->string('invoice_nip', 10)->nullable()->after('invoice_company_name');
            $table->string('invoice_vat_id')->nullable()->after('invoice_nip');
            $table->string('invoice_regon', 14)->nullable()->after('invoice_vat_id');

            // Address
            $table->string('invoice_street')->nullable()->after('invoice_regon');
            $table->string('invoice_street_number')->nullable()->after('invoice_street');
            $table->string('invoice_postal_code', 6)->nullable()->after('invoice_street_number');
            $table->string('invoice_city')->nullable()->after('invoice_postal_code');
            $table->string('invoice_country', 2)->nullable()->after('invoice_city');

            // Indexes
            $table->index('invoice_requested');
            $table->index('invoice_nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['invoice_requested']);
            $table->dropIndex(['invoice_nip']);

            $table->dropColumn([
                'invoice_requested',
                'invoice_type',
                'invoice_company_name',
                'invoice_nip',
                'invoice_vat_id',
                'invoice_regon',
                'invoice_street',
                'invoice_street_number',
                'invoice_postal_code',
                'invoice_city',
                'invoice_country',
            ]);
        });
    }
};
