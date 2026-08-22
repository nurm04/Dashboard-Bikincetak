<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenawaranItemFinishing extends Model
{
    use HasFactory;

    protected $table = 'penawaran_item_finishing';

    protected $guarded = [];

    // Relasi balik ke Penawaran Item
    public function penawaranItem()
    {
        return $this->belongsTo(PenawaranItem::class, 'id_penawaran_item', 'id');
    }

    // Relasi ke Master SKU Finishing (opsional, untuk ngecek data aslinya jika perlu)
    public function skuFinishing()
    {
        return $this->belongsTo(SkuFinishing::class, 'id_sku_finishing', 'id_sku_finishing');
    }
}
