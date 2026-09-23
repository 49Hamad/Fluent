<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Audit trail of the seat-confirmation track (staff-only). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->string('event', 40);                      // agreement_accepted, receipt_uploaded, payment_verified, …
            $table->string('actor', 20);                       // student | staff | system
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_events');
    }
};
