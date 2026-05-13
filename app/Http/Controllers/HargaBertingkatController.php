<?php

namespace App\Http\Controllers;

use App\Models\HargaBertingkat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class HargaBertingkatController extends Controller
{
    public function sync(Request $request, $id_sku)
    {
        $request->validate([
            'hargas' => 'present|array',
            'hargas.*.min' => 'required|integer|min:1',
            'hargas.*.max' => 'required|integer|gt:hargas.*.min',
            'hargas.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            HargaBertingkat::where('id_sku', $id_sku)->delete();

            foreach ($request->hargas as $item) {
                HargaBertingkat::create([
                    'id_sku' => $id_sku,
                    'min' => $item['min'],
                    'max' => $item['max'],
                    'harga' => $item['harga'],
                ]);
            }

            \DB::commit();
            return Redirect::route('produk.detailSku', $request->id_produk)
                ->with('success', 'Harga grosir berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return Redirect::back()->with('error', 'Gagal menyimpan harga.');
        }
    }
}
