<?php

namespace App\Http\Controllers;

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
        $pesananProduksi = Pesan::with([
                'customer.user',
                'pesananItem.produksiSchedules.vendor'
            ])
            ->whereIn('status_operasional', ['menunggu_diproses', 'proses_pengerjaan'])
            ->orderBy('waktu_deadline', 'asc') // Urutkan ASC (Revisi 9)
            ->get();

        $vendors = Vendor::where('is_active', true)->get();

        return Inertia::render('Produksi/Dashboard', [
            'pesananProduksi' => $pesananProduksi,
            'vendors' => $vendors
        ]);
    }

    /**
     * FASE 2: Aksi Ubah ke Proses Pengerjaan & Alokasi Tugas Multi-Vendor (Revisi 1 & 8)
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
            'alokasi.*.skema.*.description' => 'nullable|string' // Deskripsi tugas alokasi awal (Revisi 8)
        ]);

        $pesanan = Pesan::with('pesananItem.pesananItemFinishing.skuFinishing')->findOrFail($id_pesan);

        try {
            DB::beginTransaction();

            // 1. Eksekusi Pengurangan Stok Bahan Baku (Hanya jika baru pertama kali diproses)
            if ($pesanan->status_operasional === 'menunggu_diproses') {
                foreach ($pesanan->pesananItem as $item) {
                    $finishingTerpilih = collect($item->pesananItemFinishing ?? [])
                        ->map(fn($f) => $f->skuFinishing->id_pilihan_finishing ?? null)
                        ->filter()
                        ->toArray();

                    $semuaKomposisi = Komposisi::where('id_sku', $item->id_sku)->get();

                    foreach ($semuaKomposisi as $komp) {
                        if (is_null($komp->id_pilihan_finishing) || in_array($komp->id_pilihan_finishing, $finishingTerpilih)) {
                            $bahan = BahanBaku::lockForUpdate()->findOrFail($komp->id_bahan_baku);
                            $qty_dipakai = $komp->jumlah_pakai * (float) $item->jumlah;

                            $bahan->stok_sekarang -= $qty_dipakai;
                            $bahan->save();
                        }
                    }
                }
            }

            // 2. Simpan Pemecahan Qty ke Multi-Vendor / In-house
            foreach ($request->alokasi as $itemAlokasi) {
                $item = PesananItem::findOrFail($itemAlokasi['id_pesanan_item']);

                // Validasi total qty alokasi harus sama dengan qty pesanan asli
                $totalQtyInput = collect($itemAlokasi['skema'])->sum('qty_dikerjakan');
                if ($totalQtyInput != $item->jumlah) {
                    throw new \Exception("Total alokasi Qty untuk produk {$item->nama_produk_snapshot} ({$totalQtyInput}) tidak sama dengan total order ({$item->jumlah}).");
                }

                // Hapus alokasi lama jika ada re-alokasi
                PesananItemProduksi::where('id_pesanan_item', $item->id)->delete();

                // Insert baris baru pecahan pengerjaan
                foreach ($itemAlokasi['skema'] as $skema) {
                    PesananItemProduksi::create([
                        'id_pesanan_item' => $item->id,
                        'tipe_pengerjaan' => $skema['tipe_pengerjaan'],
                        'id_vendor' => $skema['id_vendor'] ?? null,
                        'qty_dikerjakan' => $skema['qty_dikerjakan'],
                        'status_pengerjaan' => 'menunggu',
                        'deskripsi_pengerjaan' => $skema['description'] ?? null // Catatan instruksi awal
                    ]);
                }
            }

            // 3. Ubah status utama pesanan ke proses pengerjaan
            $pesanan->status_operasional = 'proses_pengerjaan';
            $pesanan->save();

            DB::commit();
            return back()->with('success', 'Alokasi produksi berhasil disimpan dan status diubah ke Proses Pengerjaan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Alokasi Produksi: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * FASE 3: Aksi Ketika Vendor atau In-house Menyelesaikan Tugasnya (Revisi 8)
     */
    public function selesaikanItemProduksi(Request $request, $id_item_produksi)
    {
        $request->validate([
            'deskripsi_pengerjaan' => 'required|string', // Catatan hasil cetak
            'total_tagihan_vendor' => 'nullable|numeric|min:0', // Tagihan dari vendor (jika tipe vendor)
            'file_nota' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' // Upload nota vendor
        ]);

        $schedule = PesananItemProduksi::findOrFail($id_item_produksi);

        try {
            DB::beginTransaction();

            $pathNota = $schedule->file_nota;
            if ($request->hasFile('file_nota')) {
                $file = $request->file('file_nota');
                $filename = "NOTA-VND-" . time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();
                $pathNota = $file->storeAs('nota_vendor', $filename, 'public');
            }

            $schedule->update([
                'status_pengerjaan' => 'selesai',
                'deskripsi_pengerjaan' => $request->deskripsi_pengerjaan,
                'total_tagihan_vendor' => $schedule->tipe_pengerjaan === 'vendor' ? $request->total_tagihan_vendor : null,
                'file_nota' => $pathNota
            ]);

            DB::commit();
            return back()->with('success', 'Progress item produksi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyelesaikan item produksi: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    public function kirimPesanan($id_pesan)
    {
        $pesanan = Pesan::with('pesananItem.produksiSchedules')->findOrFail($id_pesan);

        $allSchedules = $pesanan->pesananItem->flatMap(function($item) {
            return $item->produksiSchedules;
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
}
