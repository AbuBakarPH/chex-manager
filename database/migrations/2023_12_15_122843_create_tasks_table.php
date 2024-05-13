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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('company_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('org_role_id')->comment('organizational_role_id')->nullable()->constrained();
            $table->integer('ref_id');
            $table->string('name');
            $table->string('priority');
            $table->string('status', 50);
            $table->longText('description')->nullable();
            $table->string('type');
            $table->string('frequency', 15)->nullable();
            $table->integer('created_by')->nullable();
            $table->string('admin_status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
