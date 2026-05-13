<?php

namespace App\Http\Controllers;

use App\Models\ProdukVarian;
use App\Models\ProdukSku;
use App\Models\PilihanVarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukVarianController extends Controller
{
    public function syncVarian(Request $request, $id_produk)
    {
        $request->validate([
            'selected_varians' => 'nullable|array'
        ]);

        $newVarianIds = $request->selected_varians ?? [];

        try {
            DB::beginTransaction();

            $oldVarianIds = ProdukVarian::where('id_produk', $id_produk)
                ->pluck('id_varian')
                ->toArray();

            $removedVarianIds = array_diff($oldVarianIds, $newVarianIds);

            if (!empty($removedVarianIds)) {
                $pilihanIdsToDelete = PilihanVarian::whereIn('id_varian', $removedVarianIds)
                    ->pluck('id_pilihan')
                    ->toArray();

                if (!empty($pilihanIdsToDelete)) {
                    ProdukSku::where('id_produk', $id_produk)
                        ->whereHas('skuDetailPilihan', function ($query) use ($pilihanIdsToDelete) {
                            $query->whereIn('id_pilihan', $pilihanIdsToDelete);
                        })->delete();
                }
            }

            ProdukVarian::where('id_produk', $id_produk)->delete();

            foreach ($newVarianIds as $id_varian) {
                ProdukVarian::create([
                    'id_produk' => $id_produk,
                    'id_varian' => $id_varian
                ]);
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Konfigurasi varian dan SKU berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
