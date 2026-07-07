<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->string('trigger_event')->nullable()->after('earned_on');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->string('payment_method')->nullable()->after('paid_at');
            $table->string('payment_reference')->nullable()->after('payment_method');

            $table->unique(
                ['employee_id', 'reference_type', 'reference_id'],
                'commissions_employee_reference_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('commissions_employee_reference_unique');
            $table->dropColumn(['trigger_event', 'paid_at', 'payment_method', 'payment_reference']);
        });
    }
};
