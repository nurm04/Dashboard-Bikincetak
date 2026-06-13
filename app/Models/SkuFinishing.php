<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkuFinishing extends Model
{
    use HasFactory;
    protected $table = 'sku_finishing';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_sku',
        'id_pilihan_finishing',
        'minimum_pesan',
        'harga_tambahan',
    ];

    public function pesananItemFinishing(): HasMany
    {
        return $this->hasMany(PesananItemFinishing::class, 'id_sku_finishing', 'id');
    }
    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }
    public function pilihanFinishing(): BelongsTo
    {
        return $this->belongsTo(PilihanFinishing::class, 'id_pilihan_finishing', 'id_pilihan_finishing');
    }
}
