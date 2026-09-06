<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkDesaBinaan extends Model
{
    use HasFactory;

    protected $table = 'sk_desa_binaan';

    protected $fillable = [
        'desa_id',
        'nomor_sk',
        'tanggal_sk',
        'file_sk_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(DesaBinaan::class, 'desa_id');
    }
}
