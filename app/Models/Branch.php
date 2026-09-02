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
}
