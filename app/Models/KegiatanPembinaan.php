<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KegiatanPembinaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kegiatan_pembinaan';

    protected $fillable = [
        'pimpasa_id',
        'desa_id',
        'judul',
        'jenis_pembinaan',
        'tanggal',
        'jumlah_peserta',
        'status',
        'lokasi',
        'ringkasan_materi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah_peserta' => 'integer',
        ];
    }

    public function pimpasa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpasa_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(DesaBinaan::class, 'desa_id');
    }

    public function lampiranList(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampiranable');
    }
}
