<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenawaranItem extends Model
{
    use HasFactory;

    protected $table = 'penawaran_item';

    protected $guarded = [];

    // Cast data JSON supaya otomatis jadi Array saat ditarik dari database
    protected $casts = [
        'atribut_custom_snapshot' => 'array',
        'rincian_diskon_snapshot' => 'array',
        'file_desain' => 'array', // Jika formatnya disamakan dengan pesanan_item
    ];

    // Relasi balik ke Penawaran Induk
    public function penawaran()
    {
        return $this->belongsTo(Penawaran::class, 'id_penawaran', 'id_penawaran');
    }

    // Relasi ke Master Produk SKU
    public function produkSku()
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }

    // Relasi ke Finishing (Tabel anak)
    public function penawaranItemFinishing()
    {
        return $this->hasMany(PenawaranItemFinishing::class, 'id_penawaran_item', 'id');
    }
}
