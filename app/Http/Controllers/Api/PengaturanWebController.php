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
                // Ambil nilai yang sudah di-decode dari Model
                $val = $item->nilai_parsed;

                // 1. Format URL Gambar untuk Tipe Data Image (Logo Utama, Favicon, dll)
                if ($item->tipe_data === 'image' && !empty($val)) {
                    // Cek biar nggak dobel kalau udah ada http/https
                    if (!str_starts_with($val, 'http')) {
                        $val = url('storage/' . ltrim($val, '/'));
                    }
                }

                // 2. Format URL Gambar untuk JSON Array (Contoh: Metode Pembayaran)
                if ($item->kunci === 'metode_pembayaran' && is_array($val)) {
                    foreach ($val as &$metode) {
                        // Ubah icon_url jadi absolute URL
                        if (!empty($metode['icon_url']) && !str_starts_with($metode['icon_url'], 'http')) {
                            $metode['icon_url'] = url('storage/' . ltrim($metode['icon_url'], '/'));
                        }
                    }
                    unset($metode); // Bersihkan reference
                }

                // 3. Format URL Gambar untuk JSON Object (Contoh: Data QRIS)
                if ($item->kunci === 'data_qris' && is_array($val)) {
                    if (!empty($val['gambar_url']) && !str_starts_with($val['gambar_url'], 'http')) {
                        $val['gambar_url'] = url('storage/' . ltrim($val['gambar_url'], '/'));
                    }
                }

                $formattedSettings[$item->kunci] = $val;
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
