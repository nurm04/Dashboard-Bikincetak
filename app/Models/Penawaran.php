<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penawaran extends Model
{
    use HasFactory;

    protected $table = 'penawaran';

    // Karena kita pakai format string (PP-202607-90), bukan auto increment ID
    protected $primaryKey = 'id_penawaran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    // Cast data tipe date/time supaya gampang di-format di Vue/React
    protected $casts = [
        'tanggal_penawaran' => 'datetime',
        'berlaku_sampai' => 'date',
    ];

    // Relasi ke Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    // Relasi ke Alamat (Opsional, karena di penawaran alamat bisa kosong)
    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'id_alamat', 'id_alamat');
    }

    // Relasi ke Item Penawaran
    public function penawaranItem()
    {
        return $this->hasMany(PenawaranItem::class, 'id_penawaran', 'id_penawaran');
    }

    // Relasi ke Pesanan (Hanya terisi jika penawaran ini di-ACC dan di-convert jadi pesanan asli)
    public function pesan()
    {
        return $this->belongsTo(Pesan::class, 'id_pesan_terkait', 'id_pesan');
    }
}
