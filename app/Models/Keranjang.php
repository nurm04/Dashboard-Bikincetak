<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjang';

    protected $fillable = [
        'id_customer',
        'id_sku',
        'jumlah',
        'catatan',
        'file_desain',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }

    public function keranjangFinishing(): HasMany
    {
        return $this->hasMany(KeranjangFinishing::class, 'id_keranjang', 'id');
    }
}
