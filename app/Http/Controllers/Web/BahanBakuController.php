<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $filterActive = $request->query('is_active');

        $query = BahanBaku::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nama_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('satuan', 'like', "%{$search}%")
                  ->orWhere('berat_gram_persatuan', 'like', "%{$search}%")
                  ->orWhere('harga_beli', 'like', "%{$search}%")
                  ->orWhere('stok_awal', 'like', "%{$search}%")
                  ->orWhere('stok_sekarang', 'like', "%{$search}%");
            });
        }

        if ($filterActive !== null && $filterActive !== 'semua' && $filterActive !== '') {
            $query->where('is_active', $filterActive);
        }

        return inertia('BahanBaku/Index', [
            'bahan_baku' => $query->latest()->get(),
            'filters' => $request->only(['search', 'is_active'])
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
            'berat_gram_persatuan' => 'required|string',
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
                'berat_gram_persatuan' => $request->berat_gram_persatuan,
                'harga_beli' => $request->harga_beli,
                'stok_awal' => $request->stok_awal,
                'stok_sekarang' => $request->stok_awal,
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
            'berat_gram_persatuan' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        $bahan->update([
            'nama_bahan_baku' => $request->nama_bahan_baku,
            'satuan' => $request->satuan,
            'berat_gram_persatuan' => $request->berat_gram_persatuan,
            'harga_beli' => $request->harga_beli,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku diperbarui.');
    }

    public function destroy($id)
    {
        $bahan = BahanBaku::findOrFail($id);
        $bahan->delete();
        return back()->with('success', 'Bahan baku dihapus.');
    }
}
