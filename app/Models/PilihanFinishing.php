<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilihanFinishing extends Model
{
    use HasFactory;
    protected $table = 'pilihan_finishing';
    protected $primaryKey = 'id_pilihan_finishing';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pilihan_finishing',
        'id_finishing',
        'nama_pilihan',
    ];

    public function SkuFinishing(): HasMany
    {
        return $this->hasMany(SkuFinishing::class, 'id_pilihan_finishing', 'id_pilihan_finishing');
    }
    public function finishing(): BelongsTo
    {
        return $this->belongsTo(Finishing::class, 'id_finishing', 'id_finishing');
    }
}
