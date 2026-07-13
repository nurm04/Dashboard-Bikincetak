<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'voucher';
    protected $primaryKey = 'id_voucher';

    protected $fillable = [
        'kode_voucher',
        'nama_promo',
        'tipe_target',
        'id_sku_target',
        'persentase_diskon',
        'maksimal_potongan_rupiah',
        'minimal_transaksi_rupiah',
        'kuota_penggunaan',
        'berlaku_dari',
        'berlaku_sampai',
        'is_active',
    ];

    protected $casts = [
        'berlaku_dari' => 'datetime',
        'berlaku_sampai' => 'datetime',
        'is_active' => 'boolean',
        'persentase_diskon' => 'float',
    ];

    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku_target', 'id_sku');
    }
}
