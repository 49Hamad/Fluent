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
        Schema::create('form_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_type_id')->nullable()->constrained('forms')->onDelete('cascade');
            $table->string('client_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('feedback')->nullable();
            $table->date('start_project_date')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_evaluations');
    }
};
