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
        if (!Schema::hasTable('branch_monthly_costs')) {
            Schema::create('branch_monthly_costs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                
                // Item-item pengeluaran bulanan cabang
                $table->decimal('rent_cost', 15, 2)->default(0); // Sewa Lokasi
                $table->decimal('wifi_cost', 15, 2)->default(0); // Biaya Wifi (Internet)
                $table->decimal('trash_cost', 15, 2)->default(0); // Biaya Sampah
                $table->decimal('utilities_cost', 15, 2)->default(0); // Biaya Air & Listrik
                $table->decimal('netflix_cost', 15, 2)->default(0); // Biaya Netflix
                $table->decimal('ipl_cost', 15, 2)->default(0); // IPL
                $table->decimal('salary_cost', 15, 2)->default(0); // Gaji Karyawan
                $table->decimal('other_cost', 15, 2)->default(0); // Biaya Lainnya
                $table->decimal('total_monthly_cost', 15, 2)->default(0); // Total Biaya Bulanan
                
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                // 1 cabang hanya memiliki 1 catatan cost per bulan dan tahun
                $table->unique(['branch_id', 'year', 'month'], 'branch_monthly_cost_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_monthly_costs');
    }
};
