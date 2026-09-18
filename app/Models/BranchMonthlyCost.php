<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMonthlyCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'year',
        'month',
        'rent_cost',
        'wifi_cost',
        'trash_cost',
        'utilities_cost',
        'netflix_cost',
        'ipl_cost',
        'salary_cost',
        'other_cost',
        'total_monthly_cost',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'rent_cost' => 'float',
            'wifi_cost' => 'float',
            'trash_cost' => 'float',
            'utilities_cost' => 'float',
            'netflix_cost' => 'float',
            'ipl_cost' => 'float',
            'salary_cost' => 'float',
            'other_cost' => 'float',
            'total_monthly_cost' => 'float',
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
     * Relasi ke User pembuat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nama periode bulan dan tahun (misal: September 2026)
     */
    public function getPeriodNameAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F Y');
    }

    /**
     * Hitung total biaya bulanan dari kolom-kolomnya
     */
    public function calculateTotal(): float
    {
        return (float) (
            $this->rent_cost +
            $this->wifi_cost +
            $this->trash_cost +
            $this->utilities_cost +
            $this->netflix_cost +
            $this->ipl_cost +
            $this->salary_cost +
            $this->other_cost
        );
    }
}
