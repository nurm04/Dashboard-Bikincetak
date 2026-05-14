<?php

namespace App\Http\Controllers;

use App\Models\PilihanFinishing;
use App\Services\PilihanFinishingService;
use Illuminate\Http\Request;

class PilihanFinishingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_finishing' => 'required|exists:finishing,id_finishing',
            'nama_pilihan' => 'required|string',
        ]);

        PilihanFinishing::create([
            'id_pilihan_finishing' => PilihanFinishingService::generateId($request->id_finishing),
            'id_finishing' => $request->id_finishing,
            'nama_pilihan' => $request->nama_pilihan,
        ]);

        return redirect()->back()->with('success', 'Pilihan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pilihan' => 'required|string',
        ]);

        $pilihan = PilihanFinishing::findOrFail($id);
        $pilihan->update([
            'nama_pilihan' => $request->nama_pilihan
        ]);

        return redirect()->back()->with('success', 'Pilihan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        PilihanFinishing::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Pilihan berhasil dihapus.');
    }
}
