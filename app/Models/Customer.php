<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customer';
    protected $primaryKey = 'id_customer';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_customer',
        'user_id',
        'no_hp',
        'id_role_customer',
    ];
    public function pesan(): HasMany
    {
        return $this->hasMany(Pesan::class, 'id_customer', 'id_customer');
    }
    public function alamat(): HasMany
    {
        return $this->hasMany(Alamat::class, 'id_customer', 'id_customer');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function roleCustomer(): BelongsTo
    {
        return $this->belongsTo(roleCustomer::class, 'id_role_customer', 'id_role_customer');
    }
}
