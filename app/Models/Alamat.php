<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alamat extends Model
{
    use HasFactory;
    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_alamat',
        'id_customer',

        'label',

        'nama_penerima',
        'no_hp',

        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',

        'kode_pos',

        'alamat_lengkap',

        'latitude',
        'longitude',

        'is_default',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }
}
