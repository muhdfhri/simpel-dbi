<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'desa_id',
        'upt_id',
        'nip',
        'golongan',
        'kontak',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(DesaBinaan::class, 'desa_id');
    }

    public function upt(): BelongsTo
    {
        return $this->belongsTo(Upt::class, 'upt_id');
    }

    public function desaBinaan(): HasMany
    {
        return $this->hasMany(DesaBinaan::class, 'pimpasa_id');
    }

    public function desaBinaanDipimpin(): HasMany
    {
        return $this->hasMany(DesaBinaan::class, 'pimpasa_id');
    }

    public function verifikasiLaporan(): HasMany
    {
        return $this->hasMany(LaporanVerifikasi::class, 'pimpasa_id');
    }

    public function tindakLanjutLaporan(): HasMany
    {
        return $this->hasMany(LaporanTindakLanjut::class, 'ditangani_oleh');
    }

    public function kegiatanPembinaan(): HasMany
    {
        return $this->hasMany(KegiatanPembinaan::class, 'pimpasa_id');
    }
}
