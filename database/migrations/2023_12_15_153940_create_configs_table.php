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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('task_id');
            $table->foreignId('user_id');
            $table->foreignId('team_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->enum('repeat', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->integer('repeat_count');
            $table->date('repeat_start_dd'); //(day or date)
            $table->text('exceptional_days')->nullable(); //(day or date)
            $table->text('exceptional_dates')->nullable(); //(day or date)
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
