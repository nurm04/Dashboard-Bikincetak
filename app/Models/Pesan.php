<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesan extends Model
{
    use HasFactory;
    protected $table = 'pesan';
    protected $primaryKey = 'id_pesan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pesan',
        'id_customer',
        'id_alamat',
        'tanggal_pesan',
        'tanggal_selesai',
        'status_operasional',
        'status_pembayaran',
    ];

    protected $casts = [
        'tanggal_pesan' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pesan', 'id_pesan');
    }
    public function pesananItem(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'id_pesan', 'id_pesan');
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }
    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class, 'id_alamat', 'id_alamat');
    }
}
