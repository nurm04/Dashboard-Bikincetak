<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'id_vendor';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_vendor',
        'user_id',
        'nama_vendor',
        'nama_pic',
        'no_hp',
        'alamat_lengkap',
        'nama_bank',
        'no_rekening',
        'atas_nama',
        'is_active'
    ];

    public function pesananItemProduksi(): HasMany
    {
        return $this->hasMany(PesananItemProduksi::class, 'id_vendor', 'id_vendor');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
