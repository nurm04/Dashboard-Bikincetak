<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPesanan extends Model
{
    use HasFactory;
    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_sku',
        'id_pesan',
        'jumlah',
    ];

    protected $casts = [
        'tanggal_pesan' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function pesan(): BelongsTo
    {
        return $this->belongsTo(Pesan::class, 'id_pesan', 'id_pesan');
    }
    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(produkSku::class, 'id_sku', 'id_sku');
    }
}
