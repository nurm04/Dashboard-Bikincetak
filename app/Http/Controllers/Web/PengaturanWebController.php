<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PengaturanWebController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/TampilanWeb/Index');
    }

    public function kategori()
    {
        return Inertia::render('Settings/TampilanWeb/Kategori', [
            'kategoris' => Kategori::orderBy('urutan', 'asc')->orderBy('id_kategori', 'asc')->get()
        ]);
    }

    /**
     * Bulk Update untuk menyimpan Urutan, Status, dan Icon sekaligus
     */
    public function syncKategori(Request $request)
    {
        $request->validate([
            'kategoris' => 'required|array',
            'kategoris.*.id_kategori' => 'required|exists:kategori,id_kategori',
            'kategoris.*.urutan' => 'required|integer',
            'kategoris.*.is_active' => 'required|boolean',
            'kategoris.*.icon' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->kategoris as $cat) {
                Kategori::where('id_kategori', $cat['id_kategori'])->update([
                    'urutan'    => $cat['urutan'],
                    'is_active' => $cat['is_active'],
                    'icon'      => $cat['icon']
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Urutan dan tampilan kategori berhasil disimpan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}