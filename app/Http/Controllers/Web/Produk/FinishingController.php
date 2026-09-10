<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
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
            'finishings' => Finishing::with('pilihanFinishing')->latest()->paginate(20)->withQueryString()
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
        // Validasi array of objects
        $request->validate([
            'nama_finishing' => 'required|string',
            'pilihans' => 'required|array|min:1',
            'pilihans.*.nama_pilihan' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $idV = FinishingService::generateId();

            Finishing::create([
                'id_finishing' => $idV,
                'nama_finishing' => $request->nama_finishing
            ]);

            foreach ($request->pilihans as $pilihan) {
                PilihanFinishing::create([
                    'id_pilihan_finishing' => PilihanFinishingService::generateId($idV),
                    'id_finishing' => $idV,
                    'nama_pilihan' => $pilihan['nama_pilihan'] // Ambil dari object
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
        // Validasi array of objects
        $request->validate([
            'nama_finishing' => 'required|string',
            'pilihans' => 'required|array|min:1',
            'pilihans.*.nama_pilihan' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $finishing = Finishing::findOrFail($id);

            // Update master
            $finishing->update([
                'nama_finishing' => $request->nama_finishing
            ]);

            // KUMPULKAN ID YANG VALID DI REQUEST INI
            $requestedIds = [];

            foreach ($request->pilihans as $pilihan) {
                // Jika ID sudah ada (Data Lama), LAKUKAN UPDATE!
                if (!empty($pilihan['id_pilihan_finishing'])) {
                    PilihanFinishing::where('id_pilihan_finishing', $pilihan['id_pilihan_finishing'])
                        ->where('id_finishing', $id) // Keamanan tambahan
                        ->update([
                            'nama_pilihan' => $pilihan['nama_pilihan']
                        ]);

                    $requestedIds[] = $pilihan['id_pilihan_finishing'];
                }
                // Jika ID Kosong (Baris Ditambah Baru dari Vue), LAKUKAN CREATE!
                else {
                    $newId = PilihanFinishingService::generateId($id);
                    PilihanFinishing::create([
                        'id_pilihan_finishing' => $newId,
                        'id_finishing' => $id,
                        'nama_pilihan' => $pilihan['nama_pilihan']
                    ]);

                    $requestedIds[] = $newId;
                }
            }

            // HAPUS SISA DATA YANG ADA DI DATABASE TAPI GAK DIKIRIM DARI VUE LAGI
            PilihanFinishing::where('id_finishing', $id)
                ->whereNotIn('id_pilihan_finishing', $requestedIds)
                ->delete();

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
