<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'id_staf', 'tanggal',
        'jam_masuk', 'foto_masuk', 'lat_masuk', 'long_masuk',
        'jam_keluar', 'foto_keluar', 'lat_keluar', 'long_keluar',
        'status', 'keterangan'
    ];

    public function staf()
    {
        return $this->belongsTo(Staf::class, 'id_staf', 'id_staf');
    }
}
