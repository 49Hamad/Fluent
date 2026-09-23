<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional media / photo consent — SEPARATE from the agreement.
 * Append-only: every decision (grant / decline / withdraw) is a new row,
 * the latest row is the current state; history is never deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_application_id')->constrained()->cascadeOnDelete();
            $table->boolean('granted');
            $table->text('consent_text');
            $table->string('consent_version', 30);
            $table->timestamp('decided_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['student_application_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_consents');
    }
};
