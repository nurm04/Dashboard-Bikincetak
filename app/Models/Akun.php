<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Akun extends Model
{
    use HasFactory;

    protected $table = 'akun';

    protected $primaryKey = 'id_akun';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_akun',
        'nama_akun',
        'kategori',
        'saldo_normal',
    ];

    public function bukuBesar(): HasMany
    {
        return $this->hasMany(BukuBesar::class, 'id_akun', 'id_akun');
    }
}
