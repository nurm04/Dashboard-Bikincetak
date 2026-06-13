<?php

namespace App\Services;

use App\Models\Pesan;

class PesanService
{
    public static function generateId(): string
    {
        $prefix = 'PSN-' . date('ym') . '-';

        $latest = Pesan::where('id_pesan', 'like', $prefix . '%')
                       ->orderBy('id_pesan', 'desc')
                       ->first();

        $number = $latest ? (int) substr($latest->id_pesan, -4) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
