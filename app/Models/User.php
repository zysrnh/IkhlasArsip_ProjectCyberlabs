<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'avatar', 'role', 'branch_id', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role constants
     */
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_KEPALA_CABANG = 'kepala_cabang';
    public const ROLE_ADMIN_CABANG = 'admin_cabang';
    public const ROLE_ADMIN_DAPUR = 'admin_dapur';
    public const ROLE_VIEWER = 'viewer';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Cek apakah user adalah Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Cek apakah user adalah Kepala Cabang
     */
    public function isKepalaCabang(): bool
    {
        return $this->role === self::ROLE_KEPALA_CABANG;
    }

    /**
     * Cek apakah user bisa akses semua cabang (Super Admin)
     */
    public function canAccessAllBranches(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Cek apakah user adalah Admin Cabang
     */
    public function isAdminCabang(): bool
    {
        return $this->role === self::ROLE_ADMIN_CABANG;
    }

    /**
     * Cek apakah user adalah Admin Dapur
     */
    public function isAdminDapur(): bool
    {
        return $this->role === self::ROLE_ADMIN_DAPUR;
    }

    /**
     * Relasi ke Cabang/Branch
     */
    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relasi ke Transaksi yang diinput
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * Cek apakah status user aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Cek apakah user adalah Viewer
     */
    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    /**
     * Dapatkan URL avatar jika ada
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    /**
     * Inisial nama user untuk fallback avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return strtoupper($initials ?: 'U');
    }

    /**
     * Dapatkan label role dalam format yang rapi dan user-friendly
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN => 'Super Administrator',
            self::ROLE_KEPALA_CABANG => 'Kepala Cabang',
            self::ROLE_ADMIN_CABANG => 'Admin Cabang' . ($this->branch ? ' - ' . $this->branch->name : ''),
            self::ROLE_ADMIN_DAPUR => 'Admin Dapur' . ($this->branch ? ' - ' . $this->branch->name : ''),
            self::ROLE_VIEWER => 'Viewer (Read-Only)',
            default => ucfirst(str_replace('_', ' ', $this->role ?? 'User')),
        };
    }

    /**
     * Dapatkan warna background inisial avatar berdasarkan role
     */
    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN => 'bg-purple-700 text-white',
            self::ROLE_KEPALA_CABANG => 'bg-tealBrand text-white',
            self::ROLE_ADMIN_CABANG => 'bg-navy-800 text-white border border-slate-700',
            self::ROLE_ADMIN_DAPUR => 'bg-amber-600 text-white',
            self::ROLE_VIEWER => 'bg-slate-700 text-white',
            default => 'bg-slate-700 text-white',
        };
    }
}
