<?php

namespace App\Services;

use App\Models\BukuBesar;

class BukuBesarService
{
    public function generateId(): string
    {
        $prefix = 'BB-' . date('ym') . '-';

        $latest = BukuBesar::where('id_buku_besar', 'like', $prefix . '%')
                           ->orderBy('id_buku_besar', 'desc')
                           ->first();

        $number = $latest ? (int) substr($latest->id_buku_besar, -4) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
