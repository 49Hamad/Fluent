<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student applications (طلبات الطلاب) — one per student per cohort.
 * Each approved question has its own readable column so Filament can show
 * it clearly (no raw JSON), except the multi-choice "gaps" list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('received')->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->boolean('submitted_via_waitlist')->default(false);

            // Step 1 — بياناتك (snapshot at submission time)
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('gender', 10);
            $table->string('city');
            $table->string('university');
            $table->string('major');
            $table->string('study_status', 20);
            $table->unsignedSmallInteger('graduation_year');

            // Step 2 — نبي نعرفك أكثر
            $table->text('motivation');
            $table->json('gaps');
            $table->string('gaps_other')->nullable();
            $table->boolean('has_experience');
            $table->text('experience_details')->nullable();
            $table->text('team_scenario');
            $table->string('weekly_commitment', 10);

            // Step 3 — أخيرًا
            $table->string('linkedin_url', 500)->nullable();
            $table->string('portfolio_url', 500)->nullable();
            $table->string('cv_path');                       // private disk, random file name
            $table->string('cv_original_name');
            $table->unsignedInteger('cv_size');
            $table->timestamp('consented_at');

            // Managed by the Fluent team in Filament (never shown to the student)
            $table->text('internal_notes')->nullable();

            // Interview details (shown to the student in a later phase)
            $table->dateTime('interview_at')->nullable();
            $table->string('interview_mode', 20)->nullable();       // in_person | online
            $table->string('interview_location', 500)->nullable();  // address or meeting link
            $table->text('interview_note')->nullable();

            $table->timestamps();

            $table->unique(['cohort_id', 'email']);   // no duplicate application per cohort
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_applications');
    }
};
