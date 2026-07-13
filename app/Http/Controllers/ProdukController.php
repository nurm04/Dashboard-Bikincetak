<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Varian;
use App\Models\Voucher;
use App\Services\ProdukService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Produk::with(['kategori', 'varians', 'produkSku']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_produk', 'like', "%{$search}%")
                  ->orWhere('nama_produk', 'like', "%{$search}%");
            });
        }

        return inertia('Produk/Index', [
            'produks' => $query->latest()->get(),
            'filters' => $request->only(['search'])
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
            'gambar'      => 'nullable|array',
            'gambar.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $id = ProdukService::generateId($request->id_kategori, $request->nama_produk);

        $uploadedImages = [];
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $file) {
                $uploadedImages[] = $file->store('produk', 'public');
            }
        }

        Produk::create([
            'id_produk' => $id,
            'id_kategori' => $request->id_kategori,
            'nama_produk' => $request->nama_produk,
            'gambar'      => $uploadedImages,
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
            'gambar'      => 'nullable|array',
            'gambar.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $produk = Produk::with('produkSku')->findOrFail($id);
            $namaBaru = $request->nama_produk;

            $gambarPaths = $produk->gambar ?? [];

            if ($request->hasFile('gambar')) {
                if (!empty($produk->gambar)) {
                    foreach ($produk->gambar as $oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $gambarPaths = [];
                foreach ($request->file('gambar') as $file) {
                    $gambarPaths[] = $file->store('produk', 'public');
                }
            }

            $produk->update([
                'nama_produk' => $namaBaru,
                'id_kategori' => $request->id_kategori,
                'gambar'      => $gambarPaths
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

    public function katalogWeb(Request $request)
    {
        $kategoris = Kategori::all();
        $produks = Produk::where('is_active', true)->get();
        $customers = Customer::with(['user', 'alamat', 'roleCustomer'])->get();

        $vouchers = Voucher::where('is_active', true)
            ->where('berlaku_dari', '<=', now())
            ->where('berlaku_sampai', '>=', now())
            ->get();

        return Inertia::render('Pesan/PosKasir', [
            'kategoris' => $kategoris,
            'produks' => $produks,
            'customers' => $customers,
            'vouchers' => $vouchers
        ]);
    }

    public function detailKatalogWeb(Request $request, $id_produk)
    {
        $produk = Produk::with([
            'kategori',
            'produkSku.hargaPengerjaan',
            'produkSku.hargaBertingkat',
            'produkSku.diskonCustomer',
            'produkSku.skuFinishing.pilihanFinishing.finishing'
        ])->where('is_active', true)->findOrFail($id_produk);

        return Inertia::render('Pesan/DetailProdukKasir', [
            'produk' => $produk
        ]);
    }
}
