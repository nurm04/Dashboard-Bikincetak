<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modul extends Model
{
    use HasFactory;
    protected $table = 'modul';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'nama_modul',
        'slug',
    ];

    public function hakAkses(): HasMany
    {
        return $this->hasMany(HakAkses::class, 'modul_id', 'id');
    }
}
