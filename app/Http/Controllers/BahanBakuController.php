<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BahanBakuController extends Controller
{
    public function index()
    {
        return inertia('BahanBaku/Index', [
            'bahan_baku' => BahanBaku::latest()->get()
        ]);
    }

    public function create()
    {
        return inertia('BahanBaku/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan_baku' => 'required|string|max:255',
            'satuan' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'stok_awal' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Logic Generate ID Otomatis: BAHAN-0001
            $latest = BahanBaku::orderBy('id_bahan_baku', 'desc')->first();
            $number = $latest ? (int) substr($latest->id_bahan_baku, 6) + 1 : 1;
            $id_otomatis = 'BAHAN-' . str_pad($number, 4, '0', STR_PAD_LEFT);

            BahanBaku::create([
                'id_bahan_baku' => $id_otomatis,
                'nama_bahan_baku' => $request->nama_bahan_baku,
                'satuan' => $request->satuan,
                'harga_beli' => $request->harga_beli,
                'stok_awal' => $request->stok_awal,
                'stok_sekarang' => $request->stok_awal, // Awalnya stok sekarang = stok awal
                'is_active' => true
            ]);

            DB::commit();
            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $bahan = BahanBaku::findOrFail($id);
        return inertia('BahanBaku/Form', [
            'bahan' => $bahan
        ]);
    }

    public function update(Request $request, $id)
    {
        $bahan = BahanBaku::findOrFail($id);

        $request->validate([
            'nama_bahan_baku' => 'required|string|max:255',
            'satuan' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        $bahan->update([
            'nama_bahan_baku' => $request->nama_bahan_baku,
            'satuan' => $request->satuan,
            'harga_beli' => $request->harga_beli,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku diperbarui.');
    }

    public function destroy($id)
    {
        $bahan = BahanBaku::findOrFail($id);
        // Bisa tambahkan pengecekan: Jika sudah dipake di BOM (Bill of Materials), jangan boleh hapus.
        $bahan->delete();
        return back()->with('success', 'Bahan baku dihapus.');
    }
}
