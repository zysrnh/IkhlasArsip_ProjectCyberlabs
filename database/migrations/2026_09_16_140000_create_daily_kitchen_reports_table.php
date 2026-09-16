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
        Schema::create('daily_kitchen_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('report_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('grand_total_sales', 15, 2)->default(0);
            $table->decimal('total_remaining_sellable', 15, 2)->default(0);
            $table->decimal('total_wasted_food', 15, 2)->default(0);
            $table->decimal('cash_income', 15, 2)->default(0);
            $table->decimal('qris_income', 15, 2)->default(0);
            $table->decimal('online_food_income', 15, 2)->default(0);
            $table->decimal('total_omset', 15, 2)->default(0);
            $table->decimal('difference_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('completed'); // draft, completed
            $table->timestamps();

            $table->unique(['branch_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_kitchen_reports');
    }
};
