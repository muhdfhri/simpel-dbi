<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanTindakLanjut extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_tindak_lanjut';

    protected $fillable = [
        'laporan_id',
        'nomor_registrasi',
        'seksi_penanggung_jawab',
        'bentuk_intervensi',
        'ringkasan_hasil',
        'status_akhir',
        'ditangani_oleh',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function stafUpt(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function lampiranList(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampiranable');
    }
}
