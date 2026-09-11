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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // TRX-001, dll
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'])->default('Penjualan Tunai');
            $table->string('customer_name')->default('Umum / Pelanggan');
            $table->integer('qty')->default(1);
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
