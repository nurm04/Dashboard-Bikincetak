<?php

namespace App\Services;

use App\Models\PilihanFinishing;

class PilihanFinishingService
{
    public static function generateId($id_finishing): string {

        $last = PilihanFinishing::where('id_finishing', $id_finishing)->orderBy('id_pilihan_finishing', 'desc')->first();

        if ($last) {
            $parts = explode('-', $last->id_pilihan_finishing);
            $number = (int) end($parts) + 1;
        } else {
            $number = 1;
        }

        return $id_finishing . "-" . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
