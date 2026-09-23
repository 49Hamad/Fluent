<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time sign-in codes for students ("مساحتي في Fluent").
 * The 6-digit code itself is NEVER stored — only a keyed hash of it.
 * A code is valid for 10 minutes, for one use, and for at most 5 attempts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_login_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash', 64);
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('consumed_at')->nullable();   // used, replaced, or locked after too many attempts
            $table->timestamp('created_at')->nullable();

            $table->index(['student_id', 'consumed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_login_codes');
    }
};
