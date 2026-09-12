<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BannerSlider;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function banner()
    {
        return Inertia::render('Settings/TampilanWeb/Banner', [
            'banners' => BannerSlider::orderBy('urutan', 'asc')->get()
        ]);
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'judul' => 'nullable|string|max:255',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link_tujuan' => 'nullable|string',
        ]);

        $path = $request->file('gambar')->store('banners', 'public');
        $maxUrutan = BannerSlider::max('urutan') ?? 0;

        BannerSlider::create([
            'judul' => $request->judul ?? '',
            'gambar_url' => $path,
            'link_tujuan' => $request->link_tujuan ?? '',
            'urutan' => $maxUrutan + 1,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Banner berhasil ditambahkan!');
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = BannerSlider::findOrFail($id);

        $request->validate([
            'judul' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link_tujuan' => 'nullable|string',
        ]);

        if ($request->hasFile('gambar')) {
            if ($banner->gambar_url && Storage::disk('public')->exists($banner->gambar_url)) {
                Storage::disk('public')->delete($banner->gambar_url);
            }
            $banner->gambar_url = $request->file('gambar')->store('banners', 'public');
        }

        $banner->judul = $request->judul ?? '';
        $banner->link_tujuan = $request->link_tujuan ?? '';
        $banner->save();

        return redirect()->back()->with('success', 'Banner berhasil diperbarui!');
    }

    public function destroyBanner($id)
    {
        $banner = BannerSlider::findOrFail($id);
        if ($banner->gambar_url && Storage::disk('public')->exists($banner->gambar_url)) {
            Storage::disk('public')->delete($banner->gambar_url);
        }
        $banner->delete();

        return redirect()->back()->with('success', 'Banner berhasil dihapus!');
    }

    public function syncBanner(Request $request)
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banner_slider,id',
            'banners.*.urutan' => 'required|integer',
            'banners.*.is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->banners as $b) {
                BannerSlider::where('id', $b['id'])->update([
                    'urutan'    => $b['urutan'],
                    'is_active' => $b['is_active'],
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Urutan dan status Banner berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}
