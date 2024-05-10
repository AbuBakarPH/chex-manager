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
        Schema::create('config_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id');
            $table->foreignId('user_id');
            $table->enum('type', ['staff', 'approver'])->default('staff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_assignees');
    }
};
