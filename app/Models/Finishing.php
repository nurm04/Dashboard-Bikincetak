<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finishing extends Model
{
    use HasFactory;
    protected $table = 'finishing';
    protected $primaryKey = 'id_finishing';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_finishing',
        'nama_finishing',
    ];

    public function pilihanFinishing(): HasMany
    {
        return $this->hasMany(PilihanFinishing::class, 'id_finishing', 'id_finishing');
    }
}
