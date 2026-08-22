<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuFinishingHargaBertingkat extends Model
{
    use HasFactory;

    protected $table = 'sku_finishing_harga_bertingkat';

    protected $fillable = [
        'sku_finishing_id',
        'min',
        'max',
        'tipe',
        'nilai',
    ];

    // Relasi balik ke tabel sku_finishing
    public function skuFinishing()
    {
        return $this->belongsTo(SkuFinishing::class, 'sku_finishing_id', 'id');
    }
}
