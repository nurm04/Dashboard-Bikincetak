<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
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
            'finishing.*.tipe' => 'required|in:nominal,persen',
            'finishing.*.kali_jumlah_pesan' => 'required|boolean',
            'finishing.*.harga_bertingkat' => 'nullable|array',
            'finishing.*.harga_bertingkat.*.min' => 'required_with:finishing.*.harga_bertingkat|numeric|min:1',
            'finishing.*.harga_bertingkat.*.max' => 'required_with:finishing.*.harga_bertingkat|numeric|min:0',
            'finishing.*.harga_bertingkat.*.tipe' => 'required_with:finishing.*.harga_bertingkat|in:nominal,persen',
            'finishing.*.harga_bertingkat.*.nilai' => 'required_with:finishing.*.harga_bertingkat|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            SkuFinishing::where('id_sku', $id_sku)->delete();

            foreach ($request->finishing as $item) {
                $skuFin = SkuFinishing::create([
                    'id_sku' => $id_sku,
                    'id_pilihan_finishing' => $item['id_pilihan_finishing'],
                    'minimum_pesan' => $item['minimum_pesan'],
                    'harga_tambahan' => $item['harga_tambahan'],
                    'tipe' => $item['tipe'],
                    'kali_jumlah_pesan' => $item['kali_jumlah_pesan'],
                ]);

                if (!empty($item['harga_bertingkat']) && is_array($item['harga_bertingkat'])) {
                    foreach ($item['harga_bertingkat'] as $tier) {
                        $skuFin->hargaBertingkat()->create([
                            'min' => $tier['min'],
                            'max' => $tier['max'],
                            'tipe' => $tier['tipe'],
                            'nilai' => $tier['nilai'],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('produk.detailSku', $request->id_produk)->with('success', 'Finishing SKU diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
