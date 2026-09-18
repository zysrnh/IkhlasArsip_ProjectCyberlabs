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
        'expense_raw_material',
        'expense_non_raw_material',
        'expense_personal',
        'total_expense',
        'net_cash_income',
        'expense_notes',
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
        'expense_raw_material' => 'decimal:2',
        'expense_non_raw_material' => 'decimal:2',
        'expense_personal' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_cash_income' => 'decimal:2',
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
