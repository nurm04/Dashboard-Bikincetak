<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
use App\Models\ProdukVarian;
use App\Models\ProdukSku;
use App\Models\PilihanVarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdukVarianController extends Controller
{
    public function syncVarian(Request $request, $id_produk)
    {
        // Log untuk nge-track apa yang dikirim dari Vue
        Log::info('Data Varians yang masuk:', ['data' => $request->varians]);

        // Validasi format baru yang dikirim dari Vue
        $request->validate([
            'varians'                => 'nullable|array',
            'varians.*.id_varian'    => 'required|string',
            'varians.*.jenis_varian' => 'required|in:utama,tambahan' // Pastikan string persis ini
        ]);

        $incomingVarians = $request->varians ?? [];
        // Ambil array id_varian saja untuk keperluan perbandingan / pengecekan dihapus
        $newVarianIds = array_column($incomingVarians, 'id_varian');

        try {
            DB::beginTransaction();

            $oldVarianIds = ProdukVarian::where('id_produk', $id_produk)
                ->pluck('id_varian')
                ->toArray();

            $removedVarianIds = array_diff($oldVarianIds, $newVarianIds);

            // Jika ada varian yang dihapus (Deselect), bersihkan kombinasi SKU Detailnya
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

            // Hapus relasi lama
            ProdukVarian::where('id_produk', $id_produk)->delete();

            // Insert ulang varian beserta JENIS-nya (Utama / Tambahan)
            foreach ($incomingVarians as $item) {
                ProdukVarian::create([
                    'id_produk'    => $id_produk,
                    'id_varian'    => $item['id_varian'],
                    'jenis_varian' => $item['jenis_varian'] // Pastikan data ini terbaca!
                ]);
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Konfigurasi Spesifikasi & Varian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Sync Varian: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}