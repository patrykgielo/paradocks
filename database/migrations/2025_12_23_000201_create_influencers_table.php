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
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();

            // Basic influencer information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            // Notes for internal tracking
            $table->text('notes')->nullable()
                ->comment('Internal notes about influencer partnership, agreements, etc.');

            $table->timestamps();

            // Indexes
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
