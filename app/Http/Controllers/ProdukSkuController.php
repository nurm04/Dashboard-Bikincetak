<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Finishing;
use App\Models\PilihanFinishing;
use App\Models\ProdukSku;
use App\Models\RoleCustomer;
use App\Models\SkuDetailPilihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProdukSkuController extends Controller
{
    public function syncSku(Request $request, $id_produk)
    {
        $request->validate([
            'skus' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $incomingSkus = collect($request->skus ?? []);
            $incomingIds = $incomingSkus->pluck('id_sku')->filter()->toArray();

            ProdukSku::where('id_produk', $id_produk)
                ->whereNotIn('id_sku', $incomingIds)
                ->delete();

            $lastSku = ProdukSku::where('id_produk', $id_produk)
                ->orderByRaw('LENGTH(id_sku) DESC')
                ->orderBy('id_sku', 'desc')
                ->first();

            $lastIndex = 0;
            if ($lastSku) {
                $parts = explode('-SKU-', $lastSku->id_sku);
                if (count($parts) == 2) {
                    $lastIndex = (int) $parts[1];
                }
            }

            if (!empty($request->skus)) {
                foreach ($request->skus as $data) {

                    if (!empty($data['id_sku'])) {
                        $sku = ProdukSku::where('id_sku', $data['id_sku'])->first();
                        if ($sku) {
                            $sku->update([
                                'nama_sku' => $data['nama_sku'],
                                'minimum_pesan' => $data['minimum_pesan'],
                                'harga' => $data['harga']
                            ]);

                            SkuDetailPilihan::where('id_sku', $sku->id_sku)->delete();
                            foreach ($data['pilihan_ids'] as $id_pilihan) {
                                SkuDetailPilihan::create([
                                    'id_sku' => $sku->id_sku,
                                    'id_pilihan' => $id_pilihan
                                ]);
                            }
                        }
                    } else {
                        $lastIndex++;
                        $skuId = $id_produk . "-SKU-" . str_pad($lastIndex, 3, '0', STR_PAD_LEFT);

                        ProdukSku::create([
                            'id_sku' => $skuId,
                            'id_produk' => $id_produk,
                            'nama_sku' => $data['nama_sku'],
                            'minimum_pesan' => $data['minimum_pesan'],
                            'harga' => $data['harga']
                        ]);

                        foreach ($data['pilihan_ids'] as $id_pilihan) {
                            SkuDetailPilihan::create([
                                'id_sku' => $skuId,
                                'id_pilihan' => $id_pilihan
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'SKU Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function finishing($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'skuFinishing.pilihanFinishing'])->findOrFail($id_sku);
        $finishings = Finishing::with('pilihanFinishing')->get();

        return Inertia::render('Produk/FormFinishing', [
            'sku' => $sku,
            'finishings' => $finishings,
        ]);
    }

    public function hargaBertingkat($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'hargaBertingkat'])->findOrFail($id_sku);

        return Inertia::render('Produk/FormHargaBertingkat', [
            'sku' => $sku
        ]);
    }

    public function hargaPengerjaan($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'hargaPengerjaan'])->findOrFail($id_sku);

        return Inertia::render('Produk/FormHargaPengerjaan', [
            'sku' => $sku
        ]);
    }

    public function diskonCustomer($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'diskonCustomer'])->findOrFail($id_sku);
        $roles = RoleCustomer::all();

        return Inertia::render('Produk/FormDiskonCustomer', [
            'sku' => $sku,
            'roles' => $roles
        ]);
    }

    public function komposisi($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'komposisi.bahanBaku'])->findOrFail($id_sku);
        $bahan_baku = BahanBaku::where('is_active', true)->get();

        $pilihan_finishing = PilihanFinishing::all();

        return Inertia::render('Produk/FormKomposisi', [
            'sku' => $sku,
            'bahan_baku' => $bahan_baku,
            'pilihan_finishing' => $pilihan_finishing,
        ]);
    }

    public function destroy($id_sku)
    {
        try {
            DB::beginTransaction();

            $sku = ProdukSku::findOrFail($id_sku);
            $sku->delete();

            DB::commit();
            return redirect()->back()->with('success', 'SKU dan seluruh konfigurasi harganya berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus SKU: ' . $e->getMessage());
        }
    }
}
