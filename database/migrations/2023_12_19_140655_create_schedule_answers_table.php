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
        Schema::create('schedule_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); //1
            $table->foreignId('question_id'); //8
            $table->foreignId('pivot_field_id'); //6
            $table->foreignId('schedule_id'); //1
            $table->text('answer')->nullable(); //sme Number // Some text
            $table->text('reason')->nullable(); //sme Number // Some text
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_answers');
    }
};
