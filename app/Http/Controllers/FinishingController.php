<?php

namespace App\Http\Controllers;

use App\Models\PilihanFinishing;
use App\Models\Finishing;
use App\Services\PilihanFinishingService;
use App\Services\FinishingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinishingController extends Controller
{
    public function index()
    {
        return Inertia::render('Finishing/Index', [
            'finishings' => Finishing::with('pilihanFinishing')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Finishing/Form', [
            'pilihans' => PilihanFinishing::all()
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Finishing/Form', [
            'finishing' => Finishing::with('pilihanFinishing')->findOrFail($id)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_finishing' => 'required|string',
            'pilihans' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            $idV = FinishingService::generateId();

            Finishing::create([
                'id_finishing' => $idV,
                'nama_finishing' => $request->nama_finishing
            ]);

            foreach ($request->pilihans as $index => $nama) {
                PilihanFinishing::create([
                    'id_pilihan_finishing' => PilihanFinishingService::generateId($idV),
                    'id_finishing' => $idV,
                    'nama_pilihan' => $nama
                ]);
            }

            DB::commit();
            return redirect()->route('finishing.index')->with('success', 'Data finishing dan pilihan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_finishing' => 'required|string',
            'pilihans' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            $finishing = Finishing::findOrFail($id);

            $finishing->update([
                'nama_finishing' => $request->nama_finishing
            ]);

            PilihanFinishing::where('id_finishing', $id)->delete();

            foreach ($request->pilihans as $index => $nama) {
                PilihanFinishing::create([
                    'id_pilihan_finishing' => PilihanFinishingService::generateId($id),
                    'id_finishing' => $id,
                    'nama_pilihan' => $nama
                ]);
            }

            DB::commit();
            return redirect()->route('finishing.index')->with('success', 'Data finishing berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Finishing::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Finishing berhasil dihapus.');
    }
}
