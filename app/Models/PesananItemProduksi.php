<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItemProduksi extends Model
{
    protected $table = 'pesanan_item_produksi';

    protected $fillable = [
        'id_pesanan_item', 'tipe_pengerjaan', 'id_vendor', 'qty_dikerjakan',
        'status_pengerjaan', 'deskripsi_pengerjaan', 'total_tagihan_vendor', 'file_nota'
    ];

    public function pesananItem(): BelongsTo
    {
        return $this->belongsTo(PesananItem::class, 'id_pesanan_item');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'id_vendor', 'id_vendor');
    }
}
