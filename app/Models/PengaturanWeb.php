<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanWeb extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_web';

    protected $fillable = [
        'grup',
        'kunci',
        'nilai',
        'tipe_data',
    ];

    protected $appends = ['nilai_parsed'];

    public function getNilaiParsedAttribute()
    {
        if ($this->tipe_data === 'json' && !empty($this->nilai)) {
            return json_decode($this->nilai, true);
        }

        return $this->nilai;
    }
}
