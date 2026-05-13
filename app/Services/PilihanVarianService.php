<?php

namespace App\Services;

use App\Models\PilihanVarian;

class PilihanVarianService
{
    public static function generateId($id_varian): string {

        $last = PilihanVarian::where('id_varian', $id_varian)->orderBy('id_pilihan', 'desc')->first();

        if ($last) {
            $parts = explode('-', $last->id_pilihan);
            $number = (int) end($parts) + 1;
        } else {
            $number = 1;
        }

        return $id_varian . "-" . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
