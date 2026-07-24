<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanVendor extends Model
{
    use HasFactory;
    protected $table = 'tagihan_vendor';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'kode_tagihan',
        'id_vendor',
        'total_tagihan',
        'nama_bank',
        'no_rekening',
        'atas_nama',
        'status',
        'bukti_bayar',
        'tanggal_bayar',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'total_tagihan' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'id_vendor', 'id_vendor');
    }

    public function pesananItemProduksi(): HasMany
    {
        return $this->hasMany(PesananItemProduksi::class, 'id_tagihan_vendor', 'id');
    }
}
