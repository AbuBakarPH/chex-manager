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
        Schema::create('package_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('allow_checklist');
            $table->integer('allow_checklist_config');
            $table->integer('allow_documents');
            $table->integer('allow_risk');
            $table->integer('allow_risk_config')->nullable();
            $table->integer('allow_active_ip');
            $table->integer('allow_users');
            $table->integer('allow_teams');
            $table->integer('price');
            $table->string('plan_type',100);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_plans');
    }
};
