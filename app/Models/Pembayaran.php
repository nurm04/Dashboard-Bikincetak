<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_pembayaran',
        'id_pesan',
        'nominal_bayar',
        'metode_pembayaran',
        'status_pembayaran',
        'snap_token',
        'reference_id',
        'payment_type_detail',
        'id_staf',
        'catatan',
    ];

    public function pesan()
    {
        return $this->belongsTo(Pesan::class, 'id_pesan', 'id_pesan');
    }
}
