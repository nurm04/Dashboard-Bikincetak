<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Komposisi;
use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemProduksi;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProduksiController extends Controller
{
    /**
     * FASE 1: Menampilkan Halaman Dashboard Produksi
     * Mengurutkan data berdasarkan deadline paling mepet (Revisi 9)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $vendorId = null;

        if ($user->role === 'vendor') {
            $vendorId = Vendor::where('user_id', $user->id)->value('id_vendor');
        }

        $query = Pesan::query()
            ->whereIn('status_operasional', ['menunggu_diproses', 'proses_pengerjaan'])
            ->where(function ($q) {
                $q->whereNotNull('waktu_deadline')
                  ->orWhereIn('status_pembayaran', ['dibayar_sebagian', 'lunas']);
            });

        if ($user->role === 'vendor') {
            $query->whereHas('pesananItem.pesananItemProduksi', function ($q) use ($vendorId) {
                $q->where('id_vendor', $vendorId);
            });

            $query->with([
                'customer.user',
                'pesananItem' => function ($q) use ($vendorId) {
                    $q->whereHas('pesananItemProduksi', function ($q2) use ($vendorId) {
                        $q2->where('id_vendor', $vendorId);
                    })->with([
                        'pesananItemProduksi' => function ($q3) use ($vendorId) {
                            $q3->where('id_vendor', $vendorId)->with('vendor');
                        },
                        'pesananItemFinishing'
                    ]);
                }
            ]);
        } else {
            $query->with([
                'customer.user',
                'pesananItem.pesananItemProduksi.vendor',
                'pesananItem.pesananItemFinishing'
            ]);
        }

        $pesananProduksi = $query->orderBy('waktu_deadline', 'asc')->get();
        $vendors = Vendor::where('is_active', true)->get();

        return Inertia::render('Produksi/Index', [
            'pesananProduksi' => $pesananProduksi,
            'vendors' => $vendors,
            'currentVendorId' => $vendorId
        ]);
    }

    /**
     * FASE 2: Aksi Ubah ke Proses Pengerjaan & Alokasi Tugas Multi-Vendor
     */
    public function alokasiProduksi(Request $request, $id_pesan)
    {
        $request->validate([
            'alokasi' => 'required|array|min:1',
            'alokasi.*.id_pesanan_item' => 'required|exists:pesanan_item,id',
            'alokasi.*.skema' => 'required|array|min:1',
            'alokasi.*.skema.*.tipe_pengerjaan' => 'required|in:sendiri,vendor',
            'alokasi.*.skema.*.id_vendor' => 'nullable|required_if:alokasi.*.skema.*.tipe_pengerjaan,vendor|exists:vendor,id_vendor',
            'alokasi.*.skema.*.qty_dikerjakan' => 'required|numeric|min:1',
            'alokasi.*.skema.*.instruksi_pengerjaan' => 'nullable|string'
        ]);

        $pesanan = Pesan::findOrFail($id_pesan);

        try {
            DB::beginTransaction();

            foreach ($request->alokasi as $itemAlokasi) {
                $item = PesananItem::findOrFail($itemAlokasi['id_pesanan_item']);

                $totalQtyInput = collect($itemAlokasi['skema'])->sum('qty_dikerjakan');
                if ($totalQtyInput != $item->jumlah) {
                    throw new \Exception("Total alokasi Qty untuk produk {$item->nama_produk_snapshot} ({$totalQtyInput}) tidak sama dengan total order ({$item->jumlah}).");
                }

                PesananItemProduksi::where('id_pesanan_item', $item->id)->delete();

                foreach ($itemAlokasi['skema'] as $skema) {
                    PesananItemProduksi::create([
                        'id_pesanan_item' => $item->id,
                        'tipe_pengerjaan' => $skema['tipe_pengerjaan'],
                        'id_vendor' => $skema['id_vendor'] ?? null,
                        'qty_dikerjakan' => $skema['qty_dikerjakan'],
                        'status_pengerjaan' => 'menunggu',
                        'instruksi_pengerjaan' => $skema['instruksi_pengerjaan'] ?? null,
                        'deskripsi_pengerjaan' => null
                    ]);
                }
            }

            if ($pesanan->status_operasional === 'menunggu_diproses') {
                $pesanan->status_operasional = 'proses_pengerjaan';
                $pesanan->save();
            }

            DB::commit();
            return back()->with('success', 'Alokasi produksi berhasil disimpan dan status diubah ke Proses Pengerjaan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Alokasi Produksi: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * FASE 3: Aksi Ketika Vendor atau In-house Menyelesaikan Tugasnya
     */
    public function selesaikanItemProduksi(Request $request, $id_item_produksi)
    {
        $request->validate([
            'deskripsi_pengerjaan' => 'required|string',
            'total_tagihan_vendor' => 'nullable|numeric|min:0',
            'file_nota' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'hasil_desain' => 'nullable|file|mimes:pdf,cdr,ai,psd,jpg,jpeg,png,zip,rar|max:10240'
        ]);

        $schedule = PesananItemProduksi::with('pesananItem.pesananItemFinishing.skuFinishing')->findOrFail($id_item_produksi);

        $user = auth()->user()->load('staf', 'vendor');
        $isAdmin = in_array($user->role, ['admin', 'administrator']) ||
                   ($user->staf && in_array($user->staf->role, ['admin', 'administrator']));
        $isInternal = $isAdmin || $user->role === 'staf' || $user->staf !== null;
        $vendorId = $user->vendor ? $user->vendor->id_vendor : null;

        if ($schedule->tipe_pengerjaan === 'vendor') {
            if (!$isInternal && $vendorId !== $schedule->id_vendor) {
                return back()->with('error', 'Akses ditolak! Hanya Staf Internal atau Vendor terkait yang berhak mengubah data ini.');
            }
        } else {
            if ($schedule->status_pengerjaan === 'selesai' && !$isAdmin) {
                return back()->with('error', 'Akses ditolak! Data yang telah diselesaikan hanya dapat diedit oleh Admin.');
            }
        }

        try {
            DB::beginTransaction();

            $item = $schedule->pesananItem;

            if ($schedule->tipe_pengerjaan === 'sendiri' && $schedule->status_pengerjaan !== 'selesai' && $item->id_sku !== 'SKU-CUSTOM') {

                $finishingTerpilih = collect($item->pesananItemFinishing ?? [])
                    ->map(fn($f) => $f->skuFinishing->id_pilihan_finishing ?? null)
                    ->filter()
                    ->toArray();

                $semuaKomposisi = Komposisi::where('id_sku', $item->id_sku)->get();

                foreach ($semuaKomposisi as $komp) {
                    if (is_null($komp->id_pilihan_finishing) || in_array($komp->id_pilihan_finishing, $finishingTerpilih)) {
                        $bahan = BahanBaku::lockForUpdate()->findOrFail($komp->id_bahan_baku);
                        $qty_dipakai = $komp->jumlah_pakai * (float) $schedule->qty_dikerjakan;
                        $bahan->stok_sekarang -= $qty_dipakai;
                        $bahan->save();
                    }
                }
            }

            $pathNota = $schedule->file_nota;
            if ($request->hasFile('file_nota')) {
                $file = $request->file('file_nota');
                $filename = "NOTA-VND-" . time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();
                $pathNota = $file->storeAs('nota_vendor', $filename, 'public');
            }

            $pathHasil = $schedule->hasil_desain;
            if ($request->hasFile('hasil_desain')) {
                $file = $request->file('hasil_desain');
                $filename = "HASIL-" . time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();
                $pathHasil = $file->storeAs('hasil_desain_produksi', $filename, 'public');
            }

            $schedule->update([
                'status_pengerjaan' => 'selesai',
                'deskripsi_pengerjaan' => $request->deskripsi_pengerjaan,
                'total_tagihan_vendor' => $schedule->tipe_pengerjaan === 'vendor' ? $request->total_tagihan_vendor : null,
                'file_nota' => $pathNota,
                'hasil_desain' => $pathHasil
            ]);

            DB::commit();
            return back()->with('success', 'Progress item produksi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyelesaikan item produksi: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    public function updateBerat(Request $request, $id_pesan)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id_pesanan_item' => 'required|exists:pesanan_item,id',
            'items.*.berat' => 'required|numeric|min:1',
        ]);

        foreach ($request->items as $item) {
            PesananItem::where('id', $item['id_pesanan_item'])->update([
                'total_berat_snapshot' => $item['berat']
            ]);
        }
        return back()->with('success', 'Berat produk custom berhasil disimpan.');
    }

    public function prosesPengantaran(Request $request, $id_pesan)
    {
        $request->validate([
            'ekspedisi_nama'     => 'required|string',
            'ekspedisi_layanan'  => 'nullable|string',
            'harga_ongkir'       => 'required|numeric|min:0',
            'ekspedisi_estimasi' => 'nullable|string',
        ]);

        $pesanan = Pesan::where('id_pesan', $id_pesan)->firstOrFail();

        $pesanan->update([
            'ekspedisi_nama'     => $request->ekspedisi_nama,
            'ekspedisi_layanan'  => $request->ekspedisi_layanan,
            'harga_ongkir'       => $request->harga_ongkir,
            'ekspedisi_estimasi' => $request->ekspedisi_estimasi,
            'status_operasional' => 'proses_pengantaran',
        ]);

        return back()->with('success', 'Pesanan berhasil diproses dan masuk ke tahap pengantaran.');
    }

    public function kirimPesanan($id_pesan)
    {
        $pesanan = Pesan::with('pesananItem.pesananItemProduksi')->findOrFail($id_pesan);

        $allSchedules = $pesanan->pesananItem->flatMap(function($item) {
            return $item->pesananItemProduksi;
        });

        if ($allSchedules->isEmpty()) {
            return back()->with('error', 'Pesanan ini belum dialokasikan ke jadwal produksi manapun.');
        }

        $belumSelesai = $allSchedules->contains(function($value) {
            return $value->status_pengerjaan !== 'selesai';
        });

        if ($belumSelesai) {
            return back()->with('error', 'Gagal! Masih ada item produksi yang belum selesai dikerjakan.');
        }


        $pesanan->status_operasional = 'proses_pengantaran';

        $pesanan->save();

        return back()->with('success', 'Status pesanan berhasil diubah ke Proses Pengantaran!');
    }

    public function histori(Request $request)
    {
        $user = auth()->user();
        $vendorId = null;
        $search = $request->query('search'); // Ambil query search

        if ($user->role === 'vendor') {
            $vendorId = Vendor::where('user_id', $user->id)->value('id_vendor');
        }

        $query = Pesan::query()
            ->whereIn('status_operasional', ['proses_pengantaran', 'selesai', 'diambil']);

        // TAMBAHAN: Logika Search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_pesan', 'like', "%{$search}%")
                  ->orWhere('kode_transaksi', 'like', "%{$search}%")
                  ->orWhere('kode_voucher', 'like', "%{$search}%")
                  ->orWhere('ekspedisi_nama', 'like', "%{$search}%")
                  ->orWhere('ekspedisi_layanan', 'like', "%{$search}%")
                  ->orWhere('nomor_resi', 'like', "%{$search}%")

                  ->orWhereHas('customer.user', function($qUser) use ($search) {
                      $qUser->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  })

                  ->orWhereHas('pesananItem', function($qPesananItem) use ($search) {
                      $qPesananItem->where('id_sku', 'like', "%{$search}%")
                                   ->orWhere('nama_produk_snapshot', 'like', "%{$search}%")
                                   ->orWhere('estimasi_pengerjaan_snapshot', 'like', "%{$search}%");
                  })

                  ->orWhereHas('pembayaran', function($qPembayaran) use ($search) {
                      $qPembayaran->where('metode_pembayaran', 'like', "%{$search}%");
                  })

                  ->orWhereHas('alamat', function($qAlamat) use ($search) {
                      $qAlamat->where('nama_penerima', 'like', "%{$search}%")
                              ->orWhere('no_hp', 'like', "%{$search}%")
                              ->orWhere('alamat_lengkap', 'like', "%{$search}%")
                              ->orWhere('provinsi', 'like', "%{$search}%")
                              ->orWhere('kota', 'like', "%{$search}%")
                              ->orWhere('kecamatan', 'like', "%{$search}%")
                              ->orWhere('kode_pos', 'like', "%{$search}%");
                  });
            });
        }

        if ($user->role === 'vendor') {
            $query->whereHas('pesananItem.pesananItemProduksi', function ($q) use ($vendorId) {
                $q->where('id_vendor', $vendorId);
            });

            $query->with([
                'customer.user',
                'pesananItem' => function ($q) use ($vendorId) {
                    $q->whereHas('pesananItemProduksi', function ($q2) use ($vendorId) {
                        $q2->where('id_vendor', $vendorId);
                    })->with([
                        'pesananItemProduksi' => function ($q3) use ($vendorId) {
                            $q3->where('id_vendor', $vendorId)->with(['vendor', 'tagihanVendor']);
                        },
                        'pesananItemFinishing'
                    ]);
                }
            ]);
        } else {
            $query->with([
                'customer.user',
                'pesananItem.pesananItemProduksi.vendor',
                'pesananItem.pesananItemProduksi.tagihanVendor',
                'pesananItem.pesananItemFinishing'
            ]);
        }

        // TAMBAHAN: Gunakan withQueryString() agar saat klik page 2, filter pencarian tetap ikut
        $pesananHistori = $query->orderBy('updated_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Produksi/History', [
            'pesananHistori' => $pesananHistori,
            'currentVendorId' => $vendorId,
            'filters' => $request->only(['search']) // Kirim parameter search ke Vue
        ]);
    }
}
