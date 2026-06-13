<?php

namespace App\Http\Controllers;

use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemFinishing;
use App\Models\Komposisi;
use App\Models\BahanBaku;
use App\Services\PesanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PesanController extends Controller
{
    public function index()
    {
        $pesanan = Pesan::with(['customer.user', 'pesananItem'])->latest()->get();

        return Inertia::render('Pesan/Index', [
            'pesanan' => $pesanan
        ]);
    }

    public function detail($id_pesan)
    {
        $pesanan = Pesan::with(['customer.user', 'pesananItem.pesananItemFinishing'])->findOrFail($id_pesan);

        return Inertia::render('Pesan/Detail', [
            'pesanan' => $pesanan
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_customer' => 'required|exists:customer,id_customer',
            'id_alamat' => 'required|string',
            'status_pembayaran' => 'required|in:belum_lunas,lunas',
            'items' => 'required|array|min:1',
            'items.*.id_sku' => 'required|exists:produk_sku,id_sku',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.nama_produk_snapshot' => 'required|string',
            'items.*.harga_satuan_snapshot' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $id_pesan = PesanService::generateId();

            $pesanan = Pesan::create([
                'id_pesan' => $id_pesan,
                'id_customer' => $request->id_customer,
                'id_alamat' => $request->id_alamat,
                'status_operasional' => 'menunggu_diproses',
                'status_pembayaran' => $request->status_pembayaran,
            ]);

            foreach ($request->items as $index => $item) {

                $filePath = null;
                if ($request->hasFile("items.{$index}.file_desain")) {
                    $file = $request->file("items.{$index}.file_desain");
                    $filename = $id_pesan . '_item_' . $index . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('desain_pesanan', $filename, 'public');
                }

                $pesananItem = PesananItem::create([
                    'id_pesan' => $id_pesan,
                    'id_sku' => $item['id_sku'],
                    'nama_produk_snapshot' => $item['nama_produk_snapshot'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan_snapshot' => $item['harga_satuan_snapshot'],
                    'estimasi_pengerjaan_snapshot' => $item['estimasi_pengerjaan'] ?? 'Reguler',
                    'harga_pengerjaan_snapshot' => $item['harga_pengerjaan_snapshot'] ?? 0,
                    'file_desain' => $filePath,
                    'catatan' => $item['catatan'] ?? null,
                ]);

                $finishing = isset($item['finishing']) ? json_decode($item['finishing'], true) : [];

                if (!empty($finishing) && is_array($finishing)) {
                    foreach ($finishing as $finishing) {
                        PesananItemFinishing::create([
                            'id_pesanan_item' => $pesananItem->id,
                            'id_sku_finishing' => $finishing['id_sku_finishing'],
                            'nama_finishing_snapshot' => $finishing['nama_finishing_snapshot'],
                            'harga_finishing_snapshot' => $finishing['harga_finishing_snapshot'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('pesan.index')->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getTraceAsString());

            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function updateOperasional(Request $request, $id_pesan)
    {
        $request->validate([
            'status_operasional' => 'required|in:keranjang,menunggu_diproses,proses_pengerjaan,proses_pengantaran,selesai,batal'
        ]);

        $pesanan = Pesan::with(['pesananItem.pesananItemFinishing.skuFinishing'])->findOrFail($id_pesan);
        $statusLama = $pesanan->status_operasional;
        $statusBaru = $request->status_operasional;

        try {
            DB::beginTransaction();

            if ($statusBaru === 'proses_pengerjaan' && $statusLama !== 'proses_pengerjaan') {

                $items = $pesanan->pesananItem || [];

                foreach ($items as $item) {
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

            if ($statusBaru === 'batal') {
                $pesanan->tanggal_selesai = null;

                if (in_array($statusLama, ['proses_pengerjaan', 'proses_pengantaran', 'selesai'])) {
                }
            }

            if ($statusBaru === 'selesai') {
                $pesanan->tanggal_selesai = now();
            } elseif ($statusBaru !== 'batal') {
                $pesanan->tanggal_selesai = null;
            }

            $pesanan->status_operasional = $statusBaru;
            $pesanan->save();

            DB::commit();
            return back()->with('success', 'Status Operasional berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update operasional: ' . $e->getMessage());
        }
    }

    public function updatePembayaran(Request $request, $id_pesan)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:belum_lunas,dibayar_sebagian,lunas'
        ]);

        try {
            $pesanan = Pesan::findOrFail($id_pesan);
            $pesanan->update(['status_pembayaran' => $request->status_pembayaran]);

            return back()->with('success', 'Status Pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update pembayaran: ' . $e->getMessage());
        }
    }
}
