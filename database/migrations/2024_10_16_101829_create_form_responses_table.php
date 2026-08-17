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
        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_evaluation_id')->constrained('form_evaluations')->onDelete('cascade');
            $table->foreignId('form_question_id')->constrained('form_questions')->onDelete('cascade');

            $table->enum('response_type', [
                'اجابة قصيرة', 'فقرة', 'اختيار واحد', 'متعدد الأختيارات', 'صورة', 'التقييم', 'القائمة المنسدلة'
            ])->default('اختيار واحد');

            // Different response columns based on type
            $table->text('response_text')->nullable(); // For short answer and paragraph
            $table->json('response_options')->nullable(); // For multiple choice and dropdown
            $table->integer('rating_value')->nullable(); // For rating
            $table->string('type_of_stars')->nullable(); // For rating
            $table->string('response_image')->nullable(); // For image responses


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_responses');
    }
};
