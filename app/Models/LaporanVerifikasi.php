<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanVerifikasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_verifikasi';

    protected $fillable = [
        'laporan_id',
        'pimpasa_id',
        'checklist_validitas',
        'catatan',
        'keputusan',
    ];

    protected function casts(): array
    {
        return [
            'checklist_validitas' => 'array',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function pimpasa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpasa_id');
    }
}
