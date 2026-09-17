<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'logo_path',
        'kop_header',
        'status',
    ];

    /**
     * Relasi ke User / Admin Cabang
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    /**
     * Relasi ke Transaksi Cabang
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'branch_id');
    }

    /**
     * Relasi ke Laporan Dapur Harian
     */
    public function dailyKitchenReports(): HasMany
    {
        return $this->hasMany(DailyKitchenReport::class, 'branch_id');
    }

    /**
     * Relasi ke Harga Menu Cabang
     */
    public function menuPrices(): HasMany
    {
        return $this->hasMany(BranchMenuPrice::class, 'branch_id');
    }
}
