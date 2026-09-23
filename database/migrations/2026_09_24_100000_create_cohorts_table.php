<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fluent simulation cohorts (الدفعات). New table only — no existing table is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->string('schedule')->nullable();          // e.g. "9:00 ص – 3:00 م"
            $table->string('location')->nullable();
            $table->text('instructions')->nullable();        // shown to finally-accepted students (later phase)
            $table->text('what_to_bring')->nullable();
            $table->string('registration_status', 20)->default('closed')->index(); // open | waitlist | closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
