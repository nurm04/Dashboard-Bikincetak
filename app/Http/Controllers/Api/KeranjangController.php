<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\KeranjangFinishing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KeranjangController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil customer tidak ditemukan'], 404);
        }

        $keranjang = Keranjang::with([
            'produkSku.produk',
            'keranjangFinishing.skuFinishing.pilihanFinishing'
        ])
        ->where('id_customer', $customer->id_customer)
        ->get();

        $formattedKeranjang = $keranjang->map(function ($item) {
            return [
                'id_keranjang' => $item->id,
                'id_sku' => $item->id_sku,
                'nama_produk' => $item->produkSku->produk->nama_produk ?? null,
                'nama_sku' => $item->produkSku->nama_sku ?? null,
                'jumlah' => $item->jumlah,
                'harga_dasar' => $item->produkSku->harga ?? 0,
                'catatan' => $item->catatan,

                'file_desain_url' => $item->file_desain ? url('storage/' . $item->file_desain) : null,

                'finishing' => $item->keranjangFinishing->map(function ($kf) {
                    return [
                        'id_keranjang_finishing' => $kf->id,
                        'id_sku_finishing' => $kf->id_sku_finishing,
                        'nama_finishing' => $kf->skuFinishing->pilihanFinishing->nama_pilihan ?? null,
                        'harga_tambahan' => $kf->skuFinishing->harga_tambahan ?? 0,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedKeranjang
        ], 200);
    }

    public function store(Request $request)
    {
        $customer = $request->user()->customer;

        $request->validate([
            'id_sku' => 'required|exists:produk_sku,id_sku',
            'jumlah' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
            'file_desain' => 'nullable|file|mimes:pdf,jpg,jpeg,png,cdr,zip|max:10240',

            'finishing' => 'nullable|array',
            'finishing.*' => 'exists:sku_finishing,id'
        ]);

        try {
            DB::beginTransaction();

            $pathDesain = null;
            if ($request->hasFile('file_desain')) {
                $pathDesain = $request->file('file_desain')->store('desain_keranjang', 'public');
            }

            $keranjang = Keranjang::create([
                'id_customer' => $customer->id_customer,
                'id_sku' => $request->id_sku,
                'jumlah' => $request->jumlah,
                'catatan' => $request->catatan,
                'file_desain' => $pathDesain,
            ]);

            if ($request->has('finishing')) {
                foreach ($request->finishing as $id_finishing) {
                    KeranjangFinishing::create([
                        'id_keranjang' => $keranjang->id,
                        'id_sku_finishing' => $id_finishing
                    ]);
                }
            }

            DB::commit();

            $keranjang->load([
                'produkSku.produk',
                'keranjangFinishing.skuFinishing.pilihanFinishing'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil ditambahkan ke keranjang',
                'data' => [
                    'id_keranjang' => $keranjang->id,
                    'id_sku' => $keranjang->id_sku,
                    'nama_produk' => $keranjang->produkSku->produk->nama_produk ?? null,
                    'nama_sku' => $keranjang->produkSku->nama_sku ?? null,
                    'jumlah' => $keranjang->jumlah,
                    'catatan' => $keranjang->catatan,
                    'file_desain_url' => $keranjang->file_desain ? url('storage/' . $keranjang->file_desain) : null,

                    'finishing' => $keranjang->keranjangFinishing->map(function ($kf) {
                        return [
                            'id_keranjang_finishing' => $kf->id,
                            'id_sku_finishing' => $kf->id_sku_finishing,
                            'nama_finishing' => $kf->skuFinishing->pilihanFinishing->nama_pilihan ?? null,
                            'harga_tambahan' => $kf->skuFinishing->harga_tambahan ?? 0,
                        ];
                    })
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $customer = $request->user()->customer;

        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        $keranjang = Keranjang::where('id', $id)
            ->where('id_customer', $customer->id_customer)
            ->first();

        if (!$keranjang) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan di keranjang'], 404);
        }

        $keranjang->update([
            'jumlah' => $request->jumlah,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diupdate',
            'data' => [
                'id_keranjang' => $keranjang->id,
                'jumlah_baru' => $keranjang->jumlah
            ]
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $customer = $request->user()->customer;

        $keranjang = Keranjang::where('id', $id)
            ->where('id_customer', $customer->id_customer)
            ->first();

        if (!$keranjang) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
        }

        // Hapus file desain dari storage
        if ($keranjang->file_desain) {
            Storage::disk('public')->delete($keranjang->file_desain);
        }

        $keranjang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang'
        ], 200);
    }
}
