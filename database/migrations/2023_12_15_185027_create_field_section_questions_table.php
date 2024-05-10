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
        Schema::create('field_section_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained();
            $table->foreignId('section_question_id')->constrained();
            $table->string('required')->default('in-active');
            $table->integer('sort_no')->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_section_questions');
    }
};
