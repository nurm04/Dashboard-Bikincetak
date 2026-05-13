<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelian extends Model
{
    use HasFactory;
    protected $table = 'detail_pembelian';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_pembelian',
        'id_bahan_baku',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    public function pembelianBahan(): BelongsTo
    {
        return $this->belongsTo(PembelianBahan::class, 'id_pembelian', 'id_pembelian');
    }
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }
}
