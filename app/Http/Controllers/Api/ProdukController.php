<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function getAllItems(Request $request)
    {
        $produks = Produk::with(['kategori', 'produkSku.diskonCustomer'])->get();

        $formattedProduks = $produks->map(function ($produk) {
            $hargaTermurah = $produk->produkSku->min('harga');

            $diskonRoles = [];
            if ($produk->produkSku->isNotEmpty()) {
                foreach ($produk->produkSku as $sku) {
                    foreach ($sku->diskonCustomer as $diskon) {
                        if ($diskon->tipe === 'persen') {
                            $roleId = $diskon->id_role_customer;
                            if (!isset($diskonRoles[$roleId]) || $diskon->nilai > $diskonRoles[$roleId]) {
                                $diskonRoles[$roleId] = $diskon->nilai;
                            }
                        }
                    }
                }
            }

            $gambarUrls = [];
            if (!empty($produk->gambar) && is_array($produk->gambar)) {
                $gambarUrls = array_map(function ($path) {
                    return url('storage/' . $path);
                }, $produk->gambar);
            }

            $skus = $produk->produkSku->map(function ($sku) {
                return [
                    'nama_sku' => $sku->nama_sku,
                    'harga' => $sku->harga,
                    'tipe_kalkulasi' => $sku->tipe_kalkulasi,
                ];
            })->toArray();

            return [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'kategori' => $produk->kategori ? $produk->kategori->nama_kategori : null,
                'is_active' => $produk->is_active,
                'gambar_urls' => $gambarUrls,
                'harga_mulai_dari' => $hargaTermurah ?? 0,
                'diskon_roles' => $diskonRoles,
                'dataSkus' => $skus,
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

            $gambarUrls = [];
            if (!empty($produk->gambar) && is_array($produk->gambar)) {
                $gambarUrls = array_map(function ($path) {
                    return url('storage/' . $path);
                }, $produk->gambar);
            }

            $formattedProduk = [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'kategori' => $produk->kategori ? $produk->kategori->nama_kategori : null,
                'is_active' => $produk->is_active,
                'gambar_urls' => $gambarUrls,
                'varians' => $produk->varians,

                'skus' => $produk->produkSku->map(function ($sku) {
                    return [
                        'id_sku' => $sku->id_sku,
                        'nama_sku' => $sku->nama_sku,
                        'deskripsi' => $sku->deskripsi,
                        'tipe_kalkulasi' => $sku->tipe_kalkulasi,
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
                                'tipe' => $finishing->tipe ?? 'nominal',
                                'kali_jumlah_pesan' => (bool) $finishing->kali_jumlah_pesan,
                            ];
                        })
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedProduk
            ], 200);

        } catch (ModelNotFoundException $e) {
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
