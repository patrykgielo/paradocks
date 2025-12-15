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
        Schema::create('user_invoice_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Invoice type
            $table->enum('type', ['individual', 'company', 'foreign_eu', 'foreign_non_eu'])
                ->default('individual');

            // Company details
            $table->string('company_name')->nullable();
            $table->string('nip', 10)->nullable();           // Polish tax ID
            $table->string('vat_id')->nullable();            // EU VAT ID
            $table->string('regon', 14)->nullable();         // Optional

            // Address
            $table->string('street');
            $table->string('street_number')->nullable();
            $table->string('postal_code', 6);
            $table->string('city');
            $table->string('country', 2)->default('PL');

            // Validation tracking
            $table->timestamp('validated_at')->nullable();

            // GDPR consent
            $table->timestamp('consent_given_at')->nullable();
            $table->string('consent_ip', 45)->nullable();
            $table->text('consent_user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type']);
            $table->index('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invoice_profiles');
    }
};
