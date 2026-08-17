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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string("headerlogo")->nullable();
            $table->json("social_links")->nullable();
            $table->json("color")->nullable();
            $table->json('Address')->nullable();
            $table->string('footerlogo')->nullable();
            $table->text('location')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_image')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('meta_description')->nullable();

            $table->tinyInteger("maintenance")->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
