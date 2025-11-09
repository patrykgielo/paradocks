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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index()->comment('Settings group (booking, map, contact, marketing, email)');
            $table->string('key')->index()->comment('Setting key within the group');
            $table->json('value')->comment('Setting value (can be string, number, array, etc.)');
            $table->timestamps();

            // Unique constraint on group + key combination
            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
