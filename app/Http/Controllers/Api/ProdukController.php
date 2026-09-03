<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 👇 WAJIB TAMBAH INI

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
                    'satuan' => $sku->satuan,
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
                'produkSku.hargaBertingkat',
                'produkSku.diskonCustomer',
                'produkSku.skuFinishing.pilihanFinishing.finishing',
                'produkSku.skuFinishing.hargaBertingkat'
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

                // Memaksa baca Pivot Database!
                'varians' => $produk->varians->map(function ($varian) use ($id) {
                    $jenis = $varian->pivot->jenis_varian ?? null;

                    // Kalau relasi withPivot gagal, paksa cari manual di DB
                    if (!$jenis) {
                        $pivot = DB::table('produk_varian')
                            ->where('id_produk', $id)
                            ->where('id_varian', $varian->id_varian)
                            ->first();
                        $jenis = $pivot ? $pivot->jenis_varian : 'utama';
                    }

                    return [
                        'id_varian' => $varian->id_varian,
                        'nama_varian' => $varian->nama_varian,
                        'jenis_varian' => $jenis,
                        'pilihan_varian' => $varian->pilihanVarian
                    ];
                }),

                'skus' => $produk->produkSku->map(function ($sku) {
                    // 👇 PERBAIKAN FATAL: Memastikan Gambar Menjadi ARRAY!
                    $gambarArray = [];
                    if (!empty($sku->gambar)) {
                        // Jika berupa string JSON, decode jadi array
                        if (is_string($sku->gambar)) {
                            $decoded = json_decode($sku->gambar, true);
                            $gambarArray = is_array($decoded) ? $decoded : [$sku->gambar];
                        }
                        // Jika sudah array dari cast model
                        else if (is_array($sku->gambar)) {
                            $gambarArray = $sku->gambar;
                        }
                    }

                    return [
                        'id_sku' => $sku->id_sku,
                        'nama_sku' => $sku->nama_sku,
                        'gambar' => $gambarArray, // 👈 KIRIM ARRAY YANG UDAH BERSIH
                        'satuan' => $sku->satuan,
                        'deskripsi' => $sku->deskripsi,
                        'tipe_kalkulasi' => $sku->tipe_kalkulasi,
                        'minimum_pesan' => $sku->minimum_pesan,
                        'kelipatan_pesan' => $sku->kelipatan_pesan,
                        'harga_dasar' => $sku->harga,
                        'harga_tambahan_dimensi' => $sku->harga_tambahan_dimensi,
                        'kombinasi_pilihan' => $sku->skuDetailPilihan->pluck('id_pilihan'),
                        'harga_bertingkat' => $sku->hargaBertingkat,
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
                                'kali_dimensi' => (bool) $finishing->kali_dimensi,
                                'harga_bertingkat' => $finishing->hargaBertingkat->map(function ($tier) {
                                    return [
                                        'min' => (int) $tier->min,
                                        'max' => (int) $tier->max,
                                        'tipe' => $tier->tipe,
                                        'nilai' => (float) $tier->nilai,
                                    ];
                                })->values()->toArray(),
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
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
