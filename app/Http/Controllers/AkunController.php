<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Services\AkunService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AkunController extends Controller
{
    public function index()
    {
        return Inertia::render('Akun/Index', [
            'akuns' => Akun::all()
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
