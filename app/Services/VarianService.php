<?php

namespace App\Services;

use App\Models\Varian;

class VarianService
{
    public static function generateId(): string {
        $last = Varian::orderBy('id_varian', 'desc')->first();
        $number = $last ? (int) substr($last->id_varian, 4) + 1 : 1;
        return 'VAR-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
