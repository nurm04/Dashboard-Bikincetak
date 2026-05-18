<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItemFinishing extends Model
{
    use HasFactory;
    protected $table = 'pesanan_item_finishing';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_pesanan_item',
        'id_sku_finishing',
        'nama_finishing_snapshot',
        'harga_finishing_snapshot'
    ];

    public function skuFinishing(): BelongsTo
    {
        return $this->belongsTo(SkuFinishing::class, 'id_sku_finishing', 'id_sku_finishing');
    }
    public function pesananItem(): BelongsTo
    {
        return $this->belongsTo(PesananItem::class, 'id_pesanan_item', 'id');
    }
}
