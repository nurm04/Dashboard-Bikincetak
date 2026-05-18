<?php

namespace App\Http\Controllers;

use App\Models\SkuFinishing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkuFinishingController extends Controller
{
    public function sync(Request $request, $id_sku)
    {
        $request->validate([
            'finishing' => 'present|array',
            'finishing.*.id_pilihan_finishing' => 'required',
            'finishing.*.minimum_pesan' => 'required|numeric|min:0',
            'finishing.*.harga_tambahan' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            SkuFinishing::where('id_sku', $id_sku)->delete();

            foreach ($request->finishing as $item) {
                SkuFinishing::create([
                    'id_sku' => $id_sku,
                    'id_pilihan_finishing' => $item['id_pilihan_finishing'],
                    'minimum_pesan' => $item['minimum_pesan'],
                    'harga_tambahan' => $item['harga_tambahan'],
                ]);
            }

            DB::commit();
            return redirect()->route('produk.detailSku', $request->id_produk)->with('success', 'Finishing SKU diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
