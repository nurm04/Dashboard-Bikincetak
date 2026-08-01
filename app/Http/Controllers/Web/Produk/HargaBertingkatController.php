<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
use App\Models\HargaBertingkat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class HargaBertingkatController extends Controller
{
    public function sync(Request $request, $id_sku)
    {
        $request->validate([
            'hargas' => 'present|array',
            'hargas.*.min' => 'required|integer|min:1',
            'hargas.*.max' => 'required|integer|min:0',
            'hargas.*.tipe' => 'required|in:nominal,persen',
            'hargas.*.nilai' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            HargaBertingkat::where('id_sku', $id_sku)->delete();

            foreach ($request->hargas as $item) {
                HargaBertingkat::create([
                    'id_sku' => $id_sku,
                    'min' => $item['min'],
                    'max' => $item['max'],
                    'tipe' => $item['tipe'],
                    'nilai' => $item['nilai'],
                ]);
            }

            DB::commit();
            return Redirect::route('produk.detailSku', $request->id_produk)
                ->with('success', 'Harga grosir bertingkat berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', 'Gagal menyimpan harga bertingkat: ' . $e->getMessage());
        }
    }
}
