<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesananItem extends Model
{
    use HasFactory;
    protected $table = 'pesanan_item';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_pesan',
        'id_sku',
        'nama_produk_snapshot',
        'jumlah',
        'harga_satuan_snapshot',
        'estimasi_pengerjaan_snapshot',
        'harga_pengerjaan_snapshot',
    ];

    public function pesananItemFinishing(): HasMany
    {
        return $this->hasMany(PesananItemFinishing::class, 'id_pesanan_item', 'id');
    }
    public function pesan(): BelongsTo
    {
        return $this->belongsTo(Pesan::class, 'id_pesan', 'id_pesan');
    }
    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(produkSku::class, 'id_sku', 'id_sku');
    }
}
