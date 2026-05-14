<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Varian;
use App\Services\ProdukService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProdukController extends Controller
{
    public function index()
    {
        return Inertia::render('Produk/Index', [
            'produks' => Produk::with(['kategori', 'varians', 'produkSku'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Produk/Form', [
            'kategoris' => Kategori::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'nama_produk' => 'required|string',
        ]);

        $id = ProdukService::generateId($request->id_kategori, $request->nama_produk);

        Produk::create([
            'id_produk' => $id,
            'id_kategori' => $request->id_kategori,
            'nama_produk' => $request->nama_produk,
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit($id)
    {
        return Inertia::render('Produk/Form', [
            'produk' => Produk::findOrFail($id),
            'kategoris' => Kategori::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'nama_produk' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $produk = Produk::with('produkSku')->findOrFail($id);
            $namaLama = $produk->nama_produk;
            $namaBaru = $request->nama_produk;

            $produk->update([
                'nama_produk' => $namaBaru,
                'id_kategori' => $request->id_kategori
            ]);

            foreach ($produk->produkSku as $sku) {
                $parts = explode('-', $sku->nama_sku);

                if (isset($parts[2])) {
                    $parts[2] = $namaBaru;
                    $namaSkuBaru = implode('-', $parts);
                    $sku->update([
                        'nama_sku' => $namaSkuBaru
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Data produk dan SKU berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function varian($id)
    {
        return Inertia::render('Produk/VarianSelector', [
            'produk' => Produk::with('produkVarian')->findOrFail($id),
            'master_varians' => Varian::all()
        ]);
    }

    public function sku($id)
    {
        $produk = Produk::with([
            'varians.pilihanVarian',
            'produkSku.skuDetailPilihan'
        ])->findOrFail($id);

        return Inertia::render('Produk/SkuGenerator', [
            'produk' => $produk,
        ]);
    }

    public function detailSku($id)
    {
        $produk = Produk::with([
            'produkSku.skuFinishing',
            'produkSku.hargaBertingkat',
            'produkSku.hargaPengerjaan',
            'produkSku.diskonCustomer',
            'produkSku.skuDetailPilihan.pilihanVarian'
        ])->findOrFail($id);

        return Inertia::render('Produk/DetailSku', [
            'produk' => $produk
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $produk = Produk::findOrFail($id);
            $produk->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Produk dan data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}
