<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidence of the student's electronic acceptance: the exact text shown
 * (frozen snapshot + hash), the cohort/fee details shown, and when.
 * IP / user agent are staff-only audit data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_id')->constrained()->restrictOnDelete();
            $table->string('agreement_title');
            $table->string('agreement_version', 30);
            $table->longText('content_snapshot');
            $table->json('details_snapshot');                // cohort + fee details shown with the agreement
            $table->char('content_hash', 64);                // sha256 of content + details
            $table->text('acceptance_statement');            // the checkbox text the student ticked
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_acceptances');
    }
};
