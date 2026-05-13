<?php

namespace App\Services;

use App\Models\Kategori;

class KategoriService
{
    public static function generateId(): string
    {
        $lastKategori = Kategori::orderBy('id_kategori', 'desc')->first();

        if ($lastKategori) {
            $lastNumber = (int) substr($lastKategori->id_kategori, 1);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return 'CAT-' . $nextNumber;
    }
}
