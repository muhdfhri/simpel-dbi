<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upt extends Model
{
    use HasFactory;

    protected $table = 'upt';

    protected $fillable = [
        'nama',
        'tipe',
        'wilayah_id',
    ];

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'wilayah_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'upt_id');
    }

    public function desaBinaanList(): HasMany
    {
        return $this->hasMany(DesaBinaan::class, 'upt_id');
    }
}
