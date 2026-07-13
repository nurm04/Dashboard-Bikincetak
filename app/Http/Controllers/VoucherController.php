<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\ProdukSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index()
    {
        return Inertia::render('Voucher/Index', [
            'vouchers' => Voucher::with('produkSku')->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Voucher/Form', [
            'skus' => ProdukSku::select('id_sku', 'nama_sku')->get()
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Voucher/Form', [
            'voucher' => Voucher::findOrFail($id),
            'skus' => ProdukSku::select('id_sku', 'nama_sku')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string|unique:voucher,kode_voucher',
            'nama_promo' => 'required|string',
            'tipe_target' => 'required|in:semua_pesanan,produk_tertentu',

            'id_sku_target' => 'nullable|string|required_if:tipe_target,produk_tertentu',

            'persentase_diskon' => 'required|numeric|min:0|max:100',
            'maksimal_potongan_rupiah' => 'nullable|numeric|min:0',
            'minimal_transaksi_rupiah' => 'required|numeric|min:0',
            'kuota_penggunaan' => 'nullable|integer|min:1',
            'berlaku_dari' => 'required|date',
            'berlaku_sampai' => 'required|date|after_or_equal:berlaku_dari',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            Voucher::create($request->all());

            DB::commit();
            return redirect()->route('voucher.index')->with('success', 'Voucher promo berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_voucher' => 'required|string|unique:voucher,kode_voucher,' . $id . ',id_voucher',
            'nama_promo' => 'required|string',
            'tipe_target' => 'required|in:semua_pesanan,produk_tertentu',
            'id_sku_target' => 'nullable|string|required_if:tipe_target,produk_tertentu',
            'persentase_diskon' => 'required|numeric|min:0|max:100',
            'maksimal_potongan_rupiah' => 'nullable|numeric|min:0',
            'minimal_transaksi_rupiah' => 'required|numeric|min:0',
            'kuota_penggunaan' => 'nullable|integer|min:1',
            'berlaku_dari' => 'required|date',
            'berlaku_sampai' => 'required|date|after_or_equal:berlaku_dari',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $voucher = Voucher::findOrFail($id);

            if ($request->tipe_target === 'semua_pesanan') {
                $request->merge(['id_sku_target' => null]);
            }

            $voucher->update($request->all());

            DB::commit();
            return redirect()->route('voucher.index')->with('success', 'Voucher promo berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Voucher::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Voucher berhasil dihapus.');
    }
}
