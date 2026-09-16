<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMenuPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'menu_id',
        'price',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'menu_id' => 'integer',
        'price' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
