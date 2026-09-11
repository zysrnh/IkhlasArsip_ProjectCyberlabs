<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'branch_id',
        'user_id',
        'transaction_date',
        'type',
        'customer_name',
        'qty',
        'amount',
        'notes',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'qty' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Cabang
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relasi ke User penginput
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
