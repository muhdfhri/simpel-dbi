<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriLaporan extends Model
{
    use HasFactory;

    protected $table = 'kategori_laporans';

    protected $fillable = [
        'kode',
        'nama_kategori',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi 1-to-Many ke Laporan
     */
    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class, 'kategori_id');
    }
}
