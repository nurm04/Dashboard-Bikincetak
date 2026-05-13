<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdukVarian extends Model
{
    use HasFactory;
    protected $table = 'produk_varian';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_produk',
        'id_varian',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
    public function varian(): BelongsTo
    {
        return $this->belongsTo(Varian::class, 'id_varian', 'id_varian');
    }
}
