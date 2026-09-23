<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment methods managed in Filament (طرق الدفع).
 * This release: bank transfer only. The `type` column lets another method
 * be added later without changing this table's meaning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->default('bank_transfer')->index();   // bank_transfer (only type for now)
            $table->string('name');                                           // internal label, e.g. "حساب الراجحي"
            $table->string('bank_name')->nullable();
            $table->string('beneficiary_name')->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('account_number', 40)->nullable();
            $table->text('instructions')->nullable();                         // shown to the student
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
