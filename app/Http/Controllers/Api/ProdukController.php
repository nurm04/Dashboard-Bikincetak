<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function getAllItems(Request $request)
    {
        $produks = Produk::with(['kategori'])->get();

        $formattedProduks = $produks->map(function ($produk) {
            return [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'kategori' => $produk->kategori ? $produk->kategori->nama_kategori : null,
                'is_active' => $produk->is_active,
                'gambar_url' => $produk->gambar ? url('storage/' . $produk->gambar) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedProduks
        ], 200);
    }

    public function getDetailItem($id)
    {
        try {
            $produk = Produk::with([
                'kategori',
                'varians.pilihanVarian',
                'produkSku.skuDetailPilihan',
                'produkSku.hargaPengerjaan',
                'produkSku.hargaBertingkat',
                'produkSku.diskonCustomer',
                'produkSku.skuFinishing.pilihanFinishing.finishing'
            ])
            ->where('is_active', true)
            ->findOrFail($id);


            $formattedProduk = [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'kategori' => $produk->kategori ? $produk->kategori->nama_kategori : null,
                'is_active' => $produk->is_active,
                'gambar_url' => $produk->gambar ? url('storage/' . $produk->gambar) : null,
                'varians' => $produk->varians,

                'skus' => $produk->produkSku->map(function ($sku) {
                    return [
                        'id_sku' => $sku->id_sku,
                        'nama_sku' => $sku->nama_sku,
                        'minimum_pesan' => $sku->minimum_pesan,
                        'harga_dasar' => $sku->harga,

                        'kombinasi_pilihan' => $sku->skuDetailPilihan->pluck('id_pilihan'),

                        'harga_bertingkat' => $sku->hargaBertingkat,
                        'harga_pengerjaan' => $sku->hargaPengerjaan,
                        'diskon_customer' => $sku->diskonCustomer,

                        'opsi_finishing' => $sku->skuFinishing->map(function ($finishing) {
                            return [
                                'id_sku_finishing' => $finishing->id,
                                'id_pilihan_finishing' => $finishing->id_pilihan_finishing,
                                'kategori_finishing' => $finishing->pilihanFinishing->finishing->nama_finishing ?? null,
                                'nama_pilihan' => $finishing->pilihanFinishing->nama_pilihan ?? null,
                                'minimum_pesan' => $finishing->minimum_pesan,
                                'harga_tambahan' => $finishing->harga_tambahan,
                            ];
                        })
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedProduk
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau sedang tidak aktif'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
