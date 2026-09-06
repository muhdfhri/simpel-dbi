<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'laporan_status_histories';

    public $timestamps = false; // Append-only, hanya created_at (SCHEMA.md Section 5)

    protected $fillable = [
        'laporan_id',
        'status_dari',
        'status_ke',
        'actor_id',
        'catatan',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
