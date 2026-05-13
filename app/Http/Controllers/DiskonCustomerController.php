<?php

namespace App\Http\Controllers;

use App\Models\DiskonCustomer;
use Illuminate\Http\Request;

class DiskonCustomerController extends Controller
{
    public function sync(Request $request, $id_sku)
    {
        $request->validate([
            'id_produk' => 'required',
            'diskon' => 'present|array',
            'diskon.*.id_role_customer' => 'required|exists:role_customer,id_role_customer',
            'diskon.*.tipe' => 'required|in:nominal,persen',
            'diskon.*.nilai' => 'required|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            DiskonCustomer::where('id_sku', $id_sku)->delete();

            foreach ($request->diskon as $item) {
                if ($item['nilai'] > 0) {
                    DiskonCustomer::create([
                        'id_sku' => $id_sku,
                        'id_role_customer' => $item['id_role_customer'],
                        'tipe' => $item['tipe'],
                        'nilai' => $item['nilai'],
                    ]);
                }
            }

            \DB::commit();
            return redirect()->route('produk.detailSku', $request->id_produk)->with('success', 'Diskon member berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}
