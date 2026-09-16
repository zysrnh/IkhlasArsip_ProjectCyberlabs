<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyKitchenReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'report_date',
        'user_id',
        'grand_total_sales',
        'total_remaining_sellable',
        'total_wasted_food',
        'cash_income',
        'qris_income',
        'online_food_income',
        'total_omset',
        'difference_amount',
        'notes',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'grand_total_sales' => 'decimal:2',
        'total_remaining_sellable' => 'decimal:2',
        'total_wasted_food' => 'decimal:2',
        'cash_income' => 'decimal:2',
        'qris_income' => 'decimal:2',
        'online_food_income' => 'decimal:2',
        'total_omset' => 'decimal:2',
        'difference_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyKitchenReportItem::class);
    }
}
