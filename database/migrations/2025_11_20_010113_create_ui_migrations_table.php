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
        Schema::create('ui_migrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Migration identifier (e.g., UI-MIGRATION-001)');
            $table->string('type')->comment('Migration type: ui_consolidation, feature_removal, etc.');
            $table->json('details')->nullable()->comment('Details about changes (files added/modified/deleted)');
            $table->enum('status', ['pending', 'completed', 'rolled_back'])->default('pending');
            $table->text('rollback_reason')->nullable()->comment('Reason for rollback if applicable');
            $table->timestamp('executed_at')->nullable()->comment('When migration was completed');
            $table->timestamp('rolled_back_at')->nullable()->comment('When migration was rolled back');
            $table->timestamps();

            $table->index(['status', 'executed_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ui_migrations');
    }
};
