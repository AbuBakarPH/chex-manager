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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('package_plan_id')->nullable();
            $table->string('title');
            $table->string('shifts');
            $table->string('address');
            $table->string('email');
            $table->string('phone');
            $table->boolean('allow_notification')->default(1);
            $table->boolean('allow_email')->comment("if active than user can staff recived the email")->default(1);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
