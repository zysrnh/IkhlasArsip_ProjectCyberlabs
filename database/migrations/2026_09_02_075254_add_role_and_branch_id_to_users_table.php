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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin_cabang')->after('email'); // superadmin, admin_cabang, viewer
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'branch_id', 'status']);
        });
    }
};
