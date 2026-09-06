<?php

namespace App\Models;

use App\Enums\StatusDesa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesaBinaan extends Model
{
    use HasFactory;

    protected $table = 'desa_binaan';

    protected $fillable = [
        'nama',
        'wilayah_id',
        'upt_id',
        'pimpasa_id',
        'lat',
        'lng',
        'status_terkini',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'status_terkini' => StatusDesa::class,
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'wilayah_id');
    }

    public function upt(): BelongsTo
    {
        return $this->belongsTo(Upt::class, 'upt_id');
    }

    public function pimpasa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpasa_id');
    }

    public function perangkatDesa(): HasMany
    {
        return $this->hasMany(User::class, 'desa_id');
    }

    public function skList(): HasMany
    {
        return $this->hasMany(SkDesaBinaan::class, 'desa_id');
    }

    public function laporanList(): HasMany
    {
        return $this->hasMany(Laporan::class, 'desa_id');
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'desa_id');
    }

    public function kegiatanPembinaanList(): HasMany
    {
        return $this->hasMany(KegiatanPembinaan::class, 'desa_id');
    }
}
