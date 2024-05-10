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
        Schema::create('company_package_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_plan_id');
            $table->foreignId('company_id');
            $table->boolean('is_active')->default(0);
            $table->date('subscribe_date')->comment('When Plan Subscribe');
            $table->date('expire_date')->comment('When Plan Will be Expire');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_package_plans');
    }
};
