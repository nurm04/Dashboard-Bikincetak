<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
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
            'pilihans' => 'required|array|min:1',
            'pilihans.*.nama_pilihan' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $idV = VarianService::generateId();

            Varian::create([
                'id_varian' => $idV,
                'nama_varian' => $request->nama_varian
            ]);

            // UBAH: Akses array key 'nama_pilihan'
            foreach ($request->pilihans as $item) {
                PilihanVarian::create([
                    'id_pilihan' => PilihanVarianService::generateId($idV),
                    'id_varian' => $idV,
                    'nama_pilihan' => $item['nama_pilihan']
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
            'pilihans' => 'required|array|min:1',
            'pilihans.*.nama_pilihan' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $varian = Varian::findOrFail($id);
            $varian->update([
                'nama_varian' => $request->nama_varian
            ]);

            // 1. Kumpulkan semua id_pilihan yang dikirim dari form frontend
            $requestedIds = collect($request->pilihans)->pluck('id_pilihan')->filter()->toArray();

            // 2. Cari pilihan lama di DB yang tidak dikirim lagi (artinya user hapus barisnya di frontend)
            $pilihansToDelete = PilihanVarian::where('id_varian', $id)
                                             ->whereNotIn('id_pilihan', $requestedIds)
                                             ->get();

            // 3. Proses hapus dengan try-catch khusus foreign key
            foreach ($pilihansToDelete as $del) {
                try {
                    $del->delete();
                } catch (\Illuminate\Database\QueryException $e) {
                    // Jika kena error 1451 (dipakai di sku_detail_pilihan), lempar pesan user-friendly
                    throw new \Exception("Pilihan '{$del->nama_pilihan}' tidak bisa dihapus karena sedang dipakai oleh produk.");
                }
            }

            // 4. Update nama yang diubah, atau Create jika baris baru ditambahkan
            foreach ($request->pilihans as $item) {
                if (!empty($item['id_pilihan'])) {
                    PilihanVarian::where('id_pilihan', $item['id_pilihan'])->update([
                        'nama_pilihan' => $item['nama_pilihan']
                    ]);
                } else {
                    PilihanVarian::create([
                        'id_pilihan' => PilihanVarianService::generateId($id),
                        'id_varian' => $id,
                        'nama_pilihan' => $item['nama_pilihan']
                    ]);
                }
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
