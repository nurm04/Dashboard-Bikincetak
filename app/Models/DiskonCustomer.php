<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiskonCustomer extends Model
{
    use HasFactory;
    protected $table = 'diskon_customer';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_sku',
        'id_role_customer',
        'tipe',
        'nilai'
    ];

    public function produkSku(): BelongsTo
    {
        return $this->belongsTo(ProdukSku::class, 'id_sku', 'id_sku');
    }
    public function roleCustomer(): BelongsTo
    {
        return $this->belongsTo(RoleCustomer::class, 'id_role_customer', 'id_role_customer');
    }
}
