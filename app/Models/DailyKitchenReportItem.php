<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyKitchenReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_kitchen_report_id',
        'menu_id',
        'yesterday_remaining',
        'cooked_today',
        'total_cooked',
        'sold',
        'remaining',
        'unit_price',
        'total_sales',
        'remaining_sellable_amount',
        'wasted_food_amount',
    ];

    protected $casts = [
        'yesterday_remaining' => 'integer',
        'cooked_today' => 'integer',
        'total_cooked' => 'integer',
        'sold' => 'integer',
        'remaining' => 'integer',
        'unit_price' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'remaining_sellable_amount' => 'decimal:2',
        'wasted_food_amount' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyKitchenReport::class, 'daily_kitchen_report_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
