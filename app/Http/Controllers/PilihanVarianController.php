<?php

namespace App\Http\Controllers;

use App\Models\PilihanVarian;
use App\Services\PilihanVarianService;
use Illuminate\Http\Request;

class PilihanVarianController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_varian' => 'required|exists:varian,id_varian',
            'nama_pilihan' => 'required|string',
        ]);

        PilihanVarian::create([
            'id_pilihan' => PilihanVarianService::generateId($request->id_varian),
            'id_varian' => $request->id_varian,
            'nama_pilihan' => $request->nama_pilihan,
        ]);

        return redirect()->back()->with('success', 'Pilihan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pilihan' => 'required|string',
        ]);

        $pilihan = PilihanVarian::findOrFail($id);
        $pilihan->update([
            'nama_pilihan' => $request->nama_pilihan
        ]);

        return redirect()->back()->with('success', 'Pilihan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        PilihanVarian::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Pilihan berhasil dihapus.');
    }
}
