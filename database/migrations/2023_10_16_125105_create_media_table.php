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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mediable_id')->nullable();
            $table->string('name');
            $table->boolean('status', 1)->nullable()->default(0);
            $table->text('path');
            $table->string('mime_type')->nullable();
            $table->string('mediable_type', 50)->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('type', 25);
            $table->double('size')->nullable()->comment("KB");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
