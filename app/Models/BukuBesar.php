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
        'tanggal_transaksi',
        'tipe_referensi',
        'id_referensi',
        'id_akun',
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
