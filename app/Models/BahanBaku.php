<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    use HasFactory;
    protected $table = 'bahan_baku';
    protected $primaryKey = 'id_bahan_baku';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_bahan_baku',
        'nama_bahan_baku',
        'satuan',
        'harga_beli',
        'stok_awal',
        'stok_sekarang',
        'is_active',
    ];

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'id_bahan_baku', 'id_bahan_baku');
    }
    public function komposisi(): HasMany
    {
        return $this->hasMany(Komposisi::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
