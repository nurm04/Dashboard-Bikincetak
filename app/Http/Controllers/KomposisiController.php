<?php

namespace App\Http\Controllers;

use App\Models\Komposisi;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KomposisiController extends Controller
{
    public function sync(Request $request, $id_sku)
    {
        $request->validate([
            'komposisi' => 'present|array',
            'komposisi.*.id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'komposisi.*.jumlah_pakai' => 'required|numeric|min:0.01',
            'komposisi.*.id_pilihan_finishing' => 'nullable|exists:pilihan_finishing,id_pilihan_finishing',
        ]);

        try {
            DB::beginTransaction();

            Komposisi::where('id_sku', $id_sku)->delete();

            foreach ($request->komposisi as $item) {
                $bahan = BahanBaku::findOrFail($item['id_bahan_baku']);

                $hpp_otomatis = $item['jumlah_pakai'] * $bahan->harga_beli;

                Komposisi::create([
                    'id_sku' => $id_sku,
                    'id_bahan_baku' => $item['id_bahan_baku'],
                    'id_pilihan_finishing' => $item['id_pilihan_finishing'] ?? null,
                    'jumlah_pakai' => $item['jumlah_pakai'],
                    'hpp' => $hpp_otomatis,
                ]);
            }

            DB::commit();
            return redirect()->route('produk.detailSku', $request->id_produk)->with('success', 'Resep komposisi dan HPP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
