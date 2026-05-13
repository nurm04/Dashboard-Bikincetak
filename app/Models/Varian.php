<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Varian extends Model
{
    use HasFactory;
    protected $table = 'varian';
    protected $primaryKey = 'id_varian';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_varian',
        'nama_varian',
    ];

    public function pilihanVarian(): HasMany
    {
        return $this->hasMany(PilihanVarian::class, 'id_varian', 'id_varian');
    }
    public function produkVarian(): HasMany
    {
        return $this->hasMany(ProdukVarian::class, 'id_varian', 'id_varian');
    }
}
