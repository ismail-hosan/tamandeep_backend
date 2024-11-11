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
        Schema::create('c__m__s', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['banner', 'land_first', 'land_second','work','features','footer'])->nullable();
            $table->string('title')->nullable();
            $table->string('hilight_title')->nullable();
            $table->longText('descriptions')->nullable();
            $table->string('image')->nullable();
            $table->string('first_image')->nullable();
            $table->string('first_title')->nullable();
            $table->longText('first_desc')->nullable();
            $table->string('second_image')->nullable();
            $table->string('second_title')->nullable();
            $table->longText('second_desc')->nullable();
            $table->string('third_image')->nullable();
            $table->string('third_title')->nullable();
            $table->longText ('third_desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c__m__s');
    }
};
