<?php

namespace App\Http\Controllers;

use App\Models\PilihanVarian;
use App\Models\Varian;
use App\Services\PilihanVarianService;
use App\Services\VarianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VarianController extends Controller
{
    public function index()
    {
        return Inertia::render('Varian/Index', [
            'varians' => Varian::with('pilihanVarian')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Varian/Form', [
            'pilihans' => PilihanVarian::all()
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Varian/Form', [
            'varian' => Varian::with('pilihanVarian')->findOrFail($id)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_varian' => 'required|string',
            'pilihans' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            $idV = VarianService::generateId();

            Varian::create([
                'id_varian' => $idV,
                'nama_varian' => $request->nama_varian
            ]);

            foreach ($request->pilihans as $index => $nama) {
                PilihanVarian::create([
                    'id_pilihan' => PilihanVarianService::generateId($idV),
                    'id_varian' => $idV,
                    'nama_pilihan' => $nama
                ]);
            }

            DB::commit();
            return redirect()->route('varian.index')->with('success', 'Varian dan pilihan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_varian' => 'required|string',
            'pilihans' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            $varian = Varian::findOrFail($id);

            $varian->update([
                'nama_varian' => $request->nama_varian
            ]);

            PilihanVarian::where('id_varian', $id)->delete();

            foreach ($request->pilihans as $index => $nama) {
                PilihanVarian::create([
                    'id_pilihan' => PilihanVarianService::generateId($id),
                    'id_varian' => $id,
                    'nama_pilihan' => $nama
                ]);
            }

            DB::commit();
            return redirect()->route('varian.index')->with('success', 'Varian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Varian::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Varian berhasil dihapus.');
    }
}
