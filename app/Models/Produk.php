<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'id_kategori',
        'nama_produk',
        'gambar',
        'is_active',
    ];

    public function produkSku(): HasMany
    {
        return $this->hasMany(ProdukSku::class, 'id_produk', 'id_produk');
    }
    public function produkVarian(): HasMany
    {
        return $this->hasMany(ProdukVarian::class, 'id_produk', 'id_produk');
    }
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
    public function varians()
    {
        return $this->belongsToMany(
            Varian::class,
            'produk_varian',
            'id_produk',
            'id_varian'
        );
    }
}
