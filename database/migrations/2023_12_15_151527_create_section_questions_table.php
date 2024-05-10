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
        Schema::create('section_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id');
            $table->foreignId('parent_id')->nullable();
            $table->string('title');
            $table->enum('status', ['active', 'in-active']);
            $table->integer('sort_no');
            $table->text('guidance')->nullable()->comment('used in risk assesment');
            $table->boolean('pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_questions');
    }
};
