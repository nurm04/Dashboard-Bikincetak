<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HalamanStatis extends Model
{
    use HasFactory;

    protected $table = 'halaman_statis';

    protected $fillable = [
        'judul',
        'slug',
        'konten',
        'tipe',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
