<?php

namespace App\Http\Controllers;

use App\Models\HargaPengerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class HargaPengerjaanController extends Controller
{
    public function sync(Request $request, $id_sku)
    {
        $request->validate([
            'pengerjaans' => 'present|array',
            'pengerjaans.*.pengerjaan' => 'required|string|max:255', // Contoh: "1 Hari (Express)"
            'pengerjaans.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            HargaPengerjaan::where('id_sku', $id_sku)->delete();

            foreach ($request->pengerjaans as $item) {
                HargaPengerjaan::create([
                    'id_sku' => $id_sku,
                    'pengerjaan' => $item['pengerjaan'],
                    'harga' => $item['harga'],
                ]);
            }

            \DB::commit();
            return Redirect::route('produk.detailSku', $request->id_produk)
                ->with('success', 'Estimasi harga pengerjaan berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return Redirect::back()->with('error', 'Gagal menyimpan data pengerjaan.');
        }
    }
}
