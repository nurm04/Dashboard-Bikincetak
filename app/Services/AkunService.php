<?php

namespace App\Services;

use App\Models\Akun;

class AkunService
{
    public static function generateId(string $kategori): string
    {
        $prefixMap = [
            'harta' => '1',
            'kewajiban' => '2',
            'modal' => '3',
            'pendapatan' => '4',
            'beban' => '5',
        ];

        $prefix = $prefixMap[$kategori] ?? '0';

        $lastAccount = Akun::where('kategori', $kategori)
            ->orderBy('id_akun', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = (int) substr($lastAccount->id_akun, 1);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $prefix . $nextNumber;
    }
}
