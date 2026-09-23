<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seat-confirmation track for one application (السداد وتأكيد المقعد).
 * Created when the application becomes «مقبول مبدئيًا». Kept SEPARATE from
 * the application status: payment progress never changes the application
 * status by itself — only an explicit seat confirmation does.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('payment_status', 30)->default('awaiting_transfer')->index();
            $table->timestamp('payment_status_changed_at')->nullable();

            // Snapshot of what the student was asked to pay (taken at agreement acceptance)
            $table->string('payment_method_type', 30)->nullable();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('SAR');

            $table->text('reupload_reason')->nullable();          // shown to the student
            $table->timestamp('payment_verified_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('seat_confirmed_at')->nullable();
            $table->foreignId('seat_confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('internal_notes')->nullable();           // Fluent team only
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
