<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\Kategori;

class ProdukService
{
    public static function generateId($id_kategori)
    {
        $allKategori = Kategori::orderBy('created_at', 'asc')->pluck('id_kategori')->toArray();
        $kategoriOrder = array_search($id_kategori, $allKategori) + 1;

        $productCount = Produk::where('id_kategori', $id_kategori)->count() + 1;
        $productOrder = str_pad($productCount, 3, '0', STR_PAD_LEFT);

        return "PRD-{$kategoriOrder}{$productOrder}";
    }

    public static function generateSkuId($productId, $index)
    {
        return $productId . "-SKU-" . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
    }
}
