<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItemProduksi extends Model
{
    use HasFactory;
    protected $table = 'pesanan_item_produksi';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_pesanan_item',
        'tipe_pengerjaan',
        'id_vendor',
        'id_tagihan_vendor',
        'qty_dikerjakan',
        'status_pengerjaan',
        'instruksi_pengerjaan',
        'deskripsi_pengerjaan',
        'total_tagihan_vendor',
        'file_nota'
    ];

    public function pesananItem(): BelongsTo
    {
        return $this->belongsTo(PesananItem::class, 'id_pesanan_item', 'id');
    }
    public function tagihanVendor(): BelongsTo
    {
        return $this->belongsTo(TagihanVendor::class, 'id_tagihan_vendor', 'id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'id_vendor', 'id_vendor');
    }
}
