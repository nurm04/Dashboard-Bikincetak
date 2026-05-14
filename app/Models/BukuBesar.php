<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BukuBesar extends Model
{
    use HasFactory;
    protected $table = 'buku_besar';
    protected $primaryKey = 'id_buku_besar';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_buku_besar',
        'id_akun',
        'tanggal_transaksi',
        'id_referensi',
        'tipe_referensi',
        'keterangan',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }
}
