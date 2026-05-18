<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komposisi extends Model
{
    use HasFactory;
    protected $table = 'komposisi';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_sku',
        'id_bahan_baku',
        'id_pilihan_finishing',
        'jumlah_pakai',
        'hpp',
    ];

    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(bahanBaku::class, 'id_bahan_baku', 'id_bahan_baku');
    }
    public function pilihanFinishing()
    {
        return $this->belongsTo(PilihanFinishing::class, 'id_pilihan_finishing', 'id_pilihan_finishing');
    }
}
