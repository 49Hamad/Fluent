<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versioned participation agreements (الاتفاقيات), managed in Filament.
 * cohort_id NULL = general agreement used when a cohort has no own agreement.
 * A version that has been accepted by anyone is locked (see Agreement model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('version', 30);
            $table->longText('content');            // Markdown (raw HTML is escaped when shown)
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
