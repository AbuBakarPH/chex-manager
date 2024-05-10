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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id');     // Checklist Config ID
            $table->foreignId('task_id');       // Checklist ID
            $table->foreignId('company_id');
            $table->foreignId('approved_by')->nullable();
            $table->enum('status', ['due', 'in_progress', 'requested', 'verified', 'rejected', 'in_complete'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
