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
        'alamat',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }
}
