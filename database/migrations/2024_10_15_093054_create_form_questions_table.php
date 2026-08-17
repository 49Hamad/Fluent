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
        Schema::create('form_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_type_id')->constrained('forms')->onDelete('cascade');

            $table->string('question_text');

            $table->enum('question_type', [
                'اجابة قصيرة',
                'فقرة',
                'اختيار واحد',
                'متعدد الأختيارات',
                'صورة',
                'التقييم',
                'القائمة المنسدلة'
            ])->default('اختيار واحد');

           $table->integer('number_of_stars')->nullable();
           $table->enum('type_of_stars',['قلوب','نجوم','إعجاب'])->nullable();

           $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_questions');
    }
};
