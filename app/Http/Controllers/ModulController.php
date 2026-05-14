<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModulController extends Controller
{
    public function index()
    {
        return inertia('Settings/Modul/Index', [
            'moduls' => Modul::all()
        ]);
    }

    public function create()
    {
        return inertia('Settings/Modul/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_modul' => 'required|string|max:255|unique:modul,nama_modul',
        ]);

        Modul::create([
            'nama_modul' => $request->nama_modul,
            'slug' => Str::slug($request->nama_modul)
        ]);

        return redirect()->route('hak-akses.index')->with('success', 'Modul berhasil ditambahkan.');
    }

    public function edit(Modul $modul)
    {
        return inertia('Settings/Modul/Form', [
            'modul' => $modul
        ]);
    }

    public function update(Request $request, Modul $modul)
    {
        $request->validate([
            'nama_modul' => 'required|string|max:255|unique:modul,nama_modul,' . $modul->id,
        ]);

        $modul->update([
            'nama_modul' => $request->nama_modul,
            'slug' => Str::slug($request->nama_modul)
        ]);

        return redirect()->route('hak-akses.index')->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Modul $modul)
    {
        $modul->delete();
        return back()->with('success', 'Modul berhasil dihapus.');
    }
}
