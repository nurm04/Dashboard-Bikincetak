<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkuDetailPilihan extends Model
{
    use HasFactory;
    protected $table = 'sku_detail_pilihan';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_sku',
        'id_pilihan',
    ];

    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }
    public function pilihanVarian(): BelongsTo
    {
        return $this->belongsTo(PilihanVarian::class, 'id_pilihan', 'id_pilihan');
    }
}
