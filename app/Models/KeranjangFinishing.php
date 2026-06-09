<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeranjangFinishing extends Model
{
    use HasFactory;

    protected $table = 'keranjang_finishing';

    protected $fillable = [
        'id_keranjang',
        'id_sku_finishing',
    ];

    public function keranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class, 'id_keranjang', 'id');
    }

    public function skuFinishing(): BelongsTo
    {
        return $this->belongsTo(SkuFinishing::class, 'id_sku_finishing', 'id');
    }
}
