<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'id_vendor';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_vendor', 'user_id', 'nama_vendor', 'nama_pic', 'no_hp', 'alamat_lengkap', 'is_active'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
