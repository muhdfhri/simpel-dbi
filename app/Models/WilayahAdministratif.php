<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahAdministratif extends Model
{
    use HasFactory;

    protected $table = 'wilayah_administratif';

    protected $fillable = [
        'parent_id',
        'nama',
        'level',
        'kode_kemendagri',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WilayahAdministratif::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(WilayahAdministratif::class, 'parent_id');
    }

    public function uptList(): HasMany
    {
        return $this->hasMany(Upt::class, 'wilayah_id');
    }

    public function desaBinaanList(): HasMany
    {
        return $this->hasMany(DesaBinaan::class, 'wilayah_id');
    }
}
