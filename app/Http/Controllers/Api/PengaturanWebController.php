<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerSlider;
use App\Models\HalamanStatis;
use App\Models\PengaturanWeb;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PengaturanWebController extends Controller
{
    /**
     * GET: Ambil Data Banner Slider Aktif
     */
    public function getBanners()
    {
        try {
            $banners = BannerSlider::where('is_active', true)
                ->orderBy('urutan', 'asc')
                ->get();

            // Format URL Gambar biar langsung siap pakai di Next.js
            $formattedBanners = $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'judul' => $banner->judul,
                    'gambar_url' => $banner->gambar_url ? url('storage/' . $banner->gambar_url) : null,
                    'link_tujuan' => $banner->link_tujuan,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedBanners
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET: Ambil Semua Pengaturan Web (General Settings)
     * Format outputnya dijadikan Object Key-Value agar mudah dibaca di JS (misal: data.nama_website)
     */
    public function getPengaturan()
    {
        try {
            $pengaturan = PengaturanWeb::all();

            $formattedSettings = [];
            foreach ($pengaturan as $item) {
                // nilai_parsed otomatis nge-decode JSON berkat mutator di Model lu
                $formattedSettings[$item->kunci] = $item->nilai_parsed;
            }

            return response()->json([
                'success' => true,
                'data' => $formattedSettings
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET: Ambil Daftar Halaman Statis / Detail Halaman Statis
     */
    public function getHalamanStatis($slug = null)
    {
        try {
            // Jika slug dikirim, ambil detail konten halamannya (Untuk halaman Syarat, FAQ, dll)
            if ($slug) {
                $halaman = HalamanStatis::where('slug', $slug)
                    ->where('is_active', true)
                    ->firstOrFail();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $halaman->id,
                        'judul' => $halaman->judul,
                        'slug' => $halaman->slug,
                        'tipe' => $halaman->tipe,
                        'konten' => $halaman->konten,
                        'updated_at' => $halaman->updated_at,
                    ]
                ], 200);
            }

            // Jika tanpa slug, ambil list / daftarnya saja (tanpa nge-load isi konten biar API ringan)
            $halamanList = HalamanStatis::where('is_active', true)
                ->orderBy('id', 'desc')
                ->get(['id', 'judul', 'slug', 'tipe']);

            return response()->json([
                'success' => true,
                'data' => $halamanList
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Halaman tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
