<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdukSku extends Model
{
    use HasFactory;
    protected $table = 'produk_sku';
    protected $primaryKey = 'id_sku';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_sku',
        'id_produk',
        'nama_sku',
        'minimum_pesan',
        'harga',
    ];

    public function pesananItem(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'id_sku', 'id_sku');
    }
    public function skuFinishing(): HasMany
    {
        return $this->hasMany(SkuFinishing::class, 'id_sku', 'id_sku');
    }
    public function diskonCustomer(): HasMany
    {
        return $this->hasMany(DiskonCustomer::class, 'id_sku', 'id_sku');
    }
    public function hargaBertingkat(): HasMany
    {
        return $this->hasMany(HargaBertingkat::class, 'id_sku', 'id_sku');
    }
    public function hargaPengerjaan(): HasMany
    {
        return $this->hasMany(HargaPengerjaan::class, 'id_sku', 'id_sku');
    }
    public function komposisi(): HasMany
    {
        return $this->hasMany(Komposisi::class, 'id_sku', 'id_sku');
    }
    public function skuDetailPilihan(): HasMany
    {
        return $this->hasMany(SkuDetailPilihan::class, 'id_sku', 'id_sku');
    }
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
