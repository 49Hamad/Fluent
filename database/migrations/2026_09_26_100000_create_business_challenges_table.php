<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company challenges (تحديات الشركات) — sent by companies / organisations
 * from the public «شاركنا تحديًا» form. One readable column per approved
 * question so Filament can show every answer clearly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('status', 30)->default('new')->index();
            $table->timestamp('status_changed_at')->nullable();

            // Step 1 — عن الجهة
            $table->string('org_name');
            $table->string('org_type', 20)->index();
            $table->string('org_type_other')->nullable();
            $table->string('sector');
            $table->string('contact_name');
            $table->string('job_title')->nullable();
            $table->string('email');
            $table->string('phone', 30);

            // Step 2 — عن التحدي
            $table->text('challenge_description');
            $table->text('affected_parties');
            $table->text('current_impact');
            $table->json('expected_outputs');
            $table->string('expected_outputs_other')->nullable();
            $table->boolean('can_share_materials');
            $table->boolean('has_confidential_info');
            $table->text('confidential_details')->nullable();

            // Optional file — private disk, random file name
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->unsignedInteger('file_size')->nullable();

            $table->timestamp('consented_at');

            // Managed by the Fluent team in Filament (never shown to the organisation)
            $table->text('internal_notes')->nullable();

            $table->timestamps();   // created (تاريخ الإنشاء) + last update (آخر تحديث)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_challenges');
    }
};
