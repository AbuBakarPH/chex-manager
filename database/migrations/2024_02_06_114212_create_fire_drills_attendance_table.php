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
        Schema::create('fire_drills_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attended_by')->nullable();
            $table->unsignedBigInteger('attendee_id')->nullable();
            $table->string('attendee_type', 50)->nullable();
            $table->foreignId('fire_drill_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fire_drills_attendance');
    }
};
