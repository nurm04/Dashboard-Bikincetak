<?php

namespace App\Services;

use App\Models\Finishing;

class FinishingService
{
    public static function generateId(): string {
        $last = Finishing::orderBy('id_finishing', 'desc')->first();
        $number = $last ? (int) substr($last->id_finishing, 4) + 1 : 1;
        return 'FIN-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
