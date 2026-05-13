<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoleStaf extends Model
{
    use HasFactory;
    protected $table = 'role_staf';
    protected $primaryKey = 'id_role_staf';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_role_staf',
        'role',
    ];

    public function staf(): HasMany
    {
        return $this->hasMany(Staf::class, 'id_role_staf', 'id_role_staf');
    }
}
