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
        Schema::create('question_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_question_id');
            $table->foreignId('company_id');
            $table->enum('status', ['draft', 'in_progress', 'resolved', 'completed', 'rejected'])->default('draft');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_risks');
    }
};
