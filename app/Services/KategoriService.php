<?php

namespace App\Services;

use App\Models\Kategori;

class KategoriService
{
    public static function generateId(): string
    {
        $lastKategori = Kategori::orderByRaw('LENGTH(id_kategori) DESC')
            ->orderBy('id_kategori', 'desc')
            ->first();

        if ($lastKategori) {
            $stringParts = explode('-', $lastKategori->id_kategori);
            $lastNumber = isset($stringParts[1]) ? (int) $stringParts[1] : 0;

            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return 'CAT-' . $nextNumber;
    }
}
