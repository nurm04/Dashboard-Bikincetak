<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HakAkses extends Model
{
    use HasFactory;
    protected $table = 'hak_akses';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_role_staf',
        'modul_id',
        'bisa_buka',
        'bisa_tambah',
        'bisa_ubah',
        'bisa_hapus',
    ];

    public function roleStaf(): BelongsTo
    {
        return $this->belongsTo(RoleStaf::class, 'id_role_staf', 'id_role_staf');
    }
    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'modul_id', 'id');
    }
}
