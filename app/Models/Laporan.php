<?php

namespace App\Models;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Models\KategoriLaporan as KategoriLaporanModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan';

    protected $fillable = [
        'kode_tiket',
        'desa_id',
        'kategori_id',
        'judul',
        'tanggal_kejadian',
        'lokasi_detail',
        'kronologi',
        'estimasi_jumlah_orang',
        'status',
        'submitted_at',
        'verified_at',
        'followed_up_at',
        'resolved_at',
        'sla_verifikasi_breached',
        'sla_tindak_lanjut_breached',
        'red_flag',
        'jumlah_teguran',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusLaporan::class,
            'tanggal_kejadian' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'followed_up_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_verifikasi_breached' => 'boolean',
            'sla_tindak_lanjut_breached' => 'boolean',
            'red_flag' => 'boolean',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(DesaBinaan::class, 'desa_id');
    }

    public function kategoriRef(): BelongsTo
    {
        return $this->belongsTo(KategoriLaporanModel::class, 'kategori_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriLaporanModel::class, 'kategori_id');
    }

    public function verifikasi(): HasOne
    {
        return $this->hasOne(LaporanVerifikasi::class, 'laporan_id');
    }

    public function tindakLanjut(): HasOne
    {
        return $this->hasOne(LaporanTindakLanjut::class, 'laporan_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LaporanStatusHistory::class, 'laporan_id')->orderBy('created_at', 'asc');
    }

    public function lampiranList(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampiranable');
    }
}
