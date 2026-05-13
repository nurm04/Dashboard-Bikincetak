<?php

namespace App\Http\Controllers;

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

            ProdukSku::where('id_produk', $id_produk)->delete();

            if ($request->has('skus') && !empty($request->skus)) {
                foreach ($request->skus as $index => $data) {
                    $skuId = $id_produk . "-SKU-" . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                    $sku = ProdukSku::create([
                        'id_sku' => $skuId,
                        'id_produk' => $id_produk,
                        'nama_sku' => $data['nama_sku'],
                        'harga_jasa' => $data['harga_jasa']
                    ]);

                    foreach ($data['pilihan_ids'] as $id_pilihan) {
                        SkuDetailPilihan::create([
                            'id_sku' => $skuId,
                            'id_pilihan' => $id_pilihan
                        ]);
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
