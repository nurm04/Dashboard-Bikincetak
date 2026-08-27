<?php

namespace App\Services;

use App\Models\Produk;

class ProdukService
{
    public static function generateId($id_kategori)
    {
        $kategoriNum = (int) preg_replace('/\D/', '', $id_kategori);
        $prefix = "PRD-{$kategoriNum}";
        $latestProduct = Produk::where('id_produk', 'like', $prefix . '%')
            ->orderBy('id_produk', 'desc')
            ->first();
        if ($latestProduct) {
            $lastNumber = (int) substr($latestProduct->id_produk, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $productOrder = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$productOrder}";
    }

    public static function generateSkuId($productId, $index)
    {
        return $productId . "-SKU-" . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
    }
}
