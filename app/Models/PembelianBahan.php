<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianBahan extends Model
{
    use HasFactory;
    protected $table = 'pembelian_bahan';
    protected $primaryKey = 'id_pembelian';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pembelian',
        'tanggal_beli',
        'nama_supplier',
        'total_biaya',
        'id_staf',
    ];

    protected $casts = [
        'tanggal_beli' => 'datetime',
    ];

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function staf(): BelongsTo
    {
        return $this->belongsTo(Staf::class, 'id_staf', 'id_staf');
    }
}
