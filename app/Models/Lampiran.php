<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Lampiran extends Model
{
    use HasFactory;

    protected $table = 'lampiran';

    public $timestamps = false; // Hanya created_at (SCHEMA.md Section 4)

    protected $fillable = [
        'lampiranable_id',
        'lampiranable_type',
        'path',
        'nama_file_asli',
        'tipe_file',
        'ukuran_bytes',
        'uploaded_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'ukuran_bytes' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function lampiranable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
