<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Services\KategoriService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriController extends Controller
{
    public function index()
    {
        return Inertia::render('Kategori/Index', [
            'kategoris' => Kategori::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Kategori/Form');
    }

    public function edit(Kategori $kategori)
    {
        return Inertia::render('Kategori/Form', [
            'kategori' => $kategori
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255'
        ]);

        Kategori::create([
            'id_kategori' => KategoriService::generateId(),
            'nama_kategori' => $request->nama_kategori
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dibuat dengan nama ' . $nama_kategori);
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori->update($validated);
        return redirect()->route('kategori.index')->with('success', 'Kategori ' . $kategori->nama_kategori . ' berhasil diperbarui!');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();
        return redirect()->back();
    }
}
