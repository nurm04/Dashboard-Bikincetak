<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoleCustomer extends Model
{
    use HasFactory;
    protected $table = 'role_customer';
    protected $primaryKey = 'id_role_customer';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_role_customer',
        'role',
    ];

    public function customer(): HasMany
    {
        return $this->hasMany(Customer::class, 'id_role_customer', 'id_role_customer');
    }
    public function diskonCustomer(): HasMany
    {
        return $this->hasMany(DiskonCustomer::class, 'id_role_customer', 'id_role_customer');
    }
}
