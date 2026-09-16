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
        Schema::create('daily_kitchen_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_kitchen_report_id')->constrained('daily_kitchen_reports')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->integer('yesterday_remaining')->default(0);
            $table->integer('cooked_today')->default(0);
            $table->integer('total_cooked')->default(0);
            $table->integer('sold')->default(0);
            $table->integer('remaining')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('remaining_sellable_amount', 15, 2)->default(0);
            $table->decimal('wasted_food_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_kitchen_report_items');
    }
};
