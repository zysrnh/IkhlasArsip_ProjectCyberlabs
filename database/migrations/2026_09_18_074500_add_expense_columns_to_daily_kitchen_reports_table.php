<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_kitchen_reports', function (Blueprint $table) {
            $table->decimal('expense_raw_material', 15, 2)->default(0)->after('difference_amount');
            $table->decimal('expense_non_raw_material', 15, 2)->default(0)->after('expense_raw_material');
            $table->decimal('expense_personal', 15, 2)->default(0)->after('expense_non_raw_material');
            $table->decimal('total_expense', 15, 2)->default(0)->after('expense_personal');
            $table->decimal('net_cash_income', 15, 2)->default(0)->after('total_expense');
            $table->text('expense_notes')->nullable()->after('net_cash_income');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_kitchen_reports', function (Blueprint $table) {
            $table->dropColumn([
                'expense_raw_material',
                'expense_non_raw_material',
                'expense_personal',
                'total_expense',
                'net_cash_income',
                'expense_notes',
            ]);
        });
    }
};
