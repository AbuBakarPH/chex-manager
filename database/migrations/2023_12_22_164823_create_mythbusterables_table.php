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
        Schema::create('mythbusterables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('myth_buster_id');
            $table->foreignId('mythbusterable_id');
            $table->string('mythbusterable_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mythbusterables');
    }
};
