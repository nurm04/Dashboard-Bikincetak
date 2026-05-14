<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use App\Models\HakAkses;
use App\Models\RoleStaf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HakAksesController extends Controller
{
    public function edit($id_modul)
    {
        $modul = Modul::findOrFail($id_modul);
        $roles = RoleStaf::all();

        $existingAkses = HakAkses::where('modul_id', $id_modul)->get();

        return inertia('Settings/HakAkses/Index', [
            'modul' => $modul,
            'roles' => $roles,
            'existingAkses' => $existingAkses
        ]);
    }

    public function sync(Request $request, $id_modul)
    {
        $request->validate([
            'akses' => 'required|array',
            'akses.*.id_role_staf' => 'required|exists:role_staf,id_role_staf',
        ]);

        try {
            DB::beginTransaction();

            HakAkses::where('modul_id', $id_modul)->delete();

            foreach ($request->akses as $item) {
                if ($item['bisa_buka'] || $item['bisa_tambah'] || $item['bisa_ubah'] || $item['bisa_hapus']) {
                    HakAkses::create([
                        'modul_id' => $id_modul,
                        'id_role_staf' => $item['id_role_staf'],
                        'bisa_buka' => $item['bisa_buka'],
                        'bisa_tambah' => $item['bisa_tambah'],
                        'bisa_ubah' => $item['bisa_ubah'],
                        'bisa_hapus' => $item['bisa_hapus'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('hak-akses.index')->with('success', 'Izin akses modul berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update hak akses: ' . $e->getMessage());
        }
    }
}
