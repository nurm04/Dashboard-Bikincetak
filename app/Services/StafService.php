<?php

namespace App\Services;

use App\Models\Staf;

class StafService
{
    public static function generateId(): string
    {
        $date = now()->format('Ymd');
        $lastStaf = Staf::whereDate('created_at', now())->orderBy('id_staf', 'desc')->first();

        if ($lastStaf) {
            $lastNumber = (int) substr($lastStaf->id_staf, -3);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return 'STAF-' . $date . '-' . $nextNumber;
    }
}
