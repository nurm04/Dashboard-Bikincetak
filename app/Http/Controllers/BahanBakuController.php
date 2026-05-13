<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class BahanBakuController extends Controller
{
    public function index()
    {
        return Inertia::render('BahanBaku/Index', [
            'bahanBaku' => BahanBaku::latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('BahanBaku/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_bahan_baku' => 'required|string|unique:bahan_baku,id_bahan_baku|max:50',
            'nama_bahan_baku' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'stok_awal' => 'required|numeric|min:0',
        ]);

        BahanBaku::create([
            ...$request->all(),
            'stok_sekarang' => $request->stok_awal,
        ]);

        return Redirect::route('bahan-baku.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return Inertia::render('BahanBaku/Form', [
            'bahan' => BahanBaku::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $bahan = BahanBaku::findOrFail($id);

        $request->validate([
            'nama_bahan_baku' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'stok_sekarang' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        $bahan->update($request->all());

        return Redirect::route('bahan-baku.index')->with('success', 'Data bahan baku berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $bahan = BahanBaku::findOrFail($id);
        $bahan->delete();

        return Redirect::back()->with('success', 'Bahan baku berhasil dihapus.');
    }
}
