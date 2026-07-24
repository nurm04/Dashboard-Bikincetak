<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Services\AkunService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AkunController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $filterKategori = $request->query('kategori');
        $filterSaldo = $request->query('saldo_normal');

        $query = Akun::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_akun', 'like', "%{$search}%")
                  ->orWhere('nama_akun', 'like', "%{$search}%");
            });
        }

        if (!empty($filterKategori) && $filterKategori !== 'semua') {
            $query->where('kategori', $filterKategori);
        }

        if (!empty($filterSaldo) && $filterSaldo !== 'semua') {
            $query->where('saldo_normal', $filterSaldo);
        }

        $akuns = $query->orderBy('id_akun', 'asc')->get();

        $typeKategori = DB::select("SHOW COLUMNS FROM akun WHERE Field = 'kategori'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeKategori, $matchesKategori);
        $enumKategori = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesKategori[1]));

        $typeSaldo = DB::select("SHOW COLUMNS FROM akun WHERE Field = 'saldo_normal'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeSaldo, $matchesSaldo);
        $enumSaldo = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesSaldo[1]));

        return inertia('Akun/Index', [
            'akuns' => $akuns,
            'enumKategori' => $enumKategori,
            'enumSaldo' => $enumSaldo,
            'filters' => $request->only(['search', 'kategori', 'saldo_normal'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Akun/Form');
    }

    public function edit(Akun $akun)
    {
        return Inertia::render('Akun/Form', [
            'akun' => $akun
        ]);
    }

    public function update(Request $request, Akun $akun)
    {
        $validated = $request->validate([
            'nama_akun' => 'required|string|max:255',
            'kategori' => 'required|in:harta,kewajiban,modal,pendapatan,beban',
            'saldo_normal' => 'required|in:debit,kredit',
        ]);

        $akun->update($validated);
        return redirect()->route('akun.index')->with('success', 'Akun ' . $akun->nama_akun . ' berhasil diperbarui!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_akun' => 'required|string|max:255',
            'kategori' => 'required|in:harta,kewajiban,modal,pendapatan,beban',
            'saldo_normal' => 'required|in:debit,kredit',
        ]);

        $idAkun = AkunService::generateId($validated['kategori']);

        Akun::create([
            'id_akun' => $idAkun,
            'nama_akun' => $validated['nama_akun'],
            'kategori' => $validated['kategori'],
            'saldo_normal' => $validated['saldo_normal'],
        ]);

        return redirect()->route('akun.index')->with('success', 'Akun berhasil dibuat dengan kode ' . $idAkun);
    }

    public function destroy(Akun $akun)
    {
        $akun->delete();
        return redirect()->back();
    }
}
