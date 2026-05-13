<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaBertingkat extends Model
{
    use HasFactory;
    protected $table = 'harga_bertingkat';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_sku',
        'min',
        'max',
        'harga',
    ];

    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }
}
