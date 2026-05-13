<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilihanVarian extends Model
{
    use HasFactory;
    protected $table = 'pilihan_varian';
    protected $primaryKey = 'id_pilihan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pilihan',
        'id_varian',
        'nama_pilihan',
    ];

    public function skuDetailPilihan(): HasMany
    {
        return $this->hasMany(SkuDetailPilihan::class, 'id_pilihan', 'id_pilihan');
    }
    public function varian(): BelongsTo
    {
        return $this->belongsTo(Varian::class, 'id_varian', 'id_varian');
    }
}
