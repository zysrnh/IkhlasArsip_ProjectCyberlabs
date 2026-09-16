<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'name',
        'is_perishable',
        'default_price',
        'is_active',
    ];

    protected $casts = [
        'order_number' => 'integer',
        'is_perishable' => 'boolean',
        'default_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke harga menu per cabang
     */
    public function branchPrices(): HasMany
    {
        return $this->hasMany(BranchMenuPrice::class, 'menu_id');
    }

    /**
     * Dapatkan harga menu untuk cabang tertentu (fallback ke default_price)
     */
    public function getPriceForBranch(int $branchId): float
    {
        $branchPrice = $this->branchPrices->firstWhere('branch_id', $branchId);
        if ($branchPrice && $branchPrice->price > 0) {
            return (float) $branchPrice->price;
        }

        return (float) $this->default_price;
    }
}
