<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Fee + payment configuration per cohort (الرسوم والسداد). New nullable columns only. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cohorts', function (Blueprint $table) {
            $table->decimal('fee_amount', 10, 2)->nullable()->after('what_to_bring');
            $table->string('fee_currency', 3)->default('SAR')->after('fee_amount');
            $table->date('payment_deadline')->nullable()->after('fee_currency');
            $table->foreignId('payment_method_id')->nullable()->after('payment_deadline')
                ->constrained('payment_methods')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cohorts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn(['fee_amount', 'fee_currency', 'payment_deadline']);
        });
    }
};
