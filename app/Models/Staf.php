<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staf extends Model
{
    use HasFactory;
    protected $table = 'staf';
    protected $primaryKey = 'id_staf';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_staf',
        'user_id',
        'no_hp',
        'id_role_staf',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function roleStaf(): BelongsTo
    {
        return $this->belongsTo(RoleStaf::class, 'id_role_staf', 'id_role_staf');
    }
}
