<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Komposisi;
use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemFinishing;
use App\Models\SkuFinishing;
use App\Services\PembayaranService;
use App\Services\PesanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PesanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $filterPembayaran = $request->query('status_pembayaran');
        $filterOperasional = $request->query('status_operasional');

        $query = Pesan::with([
                'customer.user',
                'pembayaran',
                'pesananItem.pesananItemFinishing',
            ])
            ->whereNotIn('status_operasional', ['keranjang']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_pesan', 'like', "%{$search}%")
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

        if (!empty($filterOperasional) && $filterOperasional !== 'semua') {
            $query->where('status_operasional', $filterOperasional);
        }

        if (!empty($filterPembayaran) && $filterPembayaran !== 'semua') {
            $query->where('status_pembayaran', $filterPembayaran);
        }

        $pesanan = $query->latest()
            ->get()
            ->map(function ($pesan) {
                $pesan->total_tagihan = PesanService::hitungTotalPesanan($pesan);
                $pesan->total_dibayar = $pesan->pembayaran
                    ->where('status_pembayaran', 'berhasil')
                    ->sum('nominal_bayar');

                return $pesan;
            });

        $typePembayaran = DB::select("SHOW COLUMNS FROM pesan WHERE Field = 'status_pembayaran'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typePembayaran, $matchesPembayaran);
        $enumPembayaran = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesPembayaran[1]));

        $typeOperasional = DB::select("SHOW COLUMNS FROM pesan WHERE Field = 'status_operasional'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeOperasional, $matchesOperasional);
        $enumOperasional = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesOperasional[1]));

        return Inertia::render('Pesan/Index', [
            'pesanan' => $pesanan,
            'enumPembayaran' => $enumPembayaran,
            'enumOperasional' => $enumOperasional,
            'filters' => $request->only(['search', 'status_pembayaran', 'status_operasional'])
        ]);
    }

    public function detail($id_pesan)
    {
        $pesanan = Pesan::with([
                'customer.user',
                'pesananItem.pesananItemFinishing',
                'alamat',
                'pembayaran'
            ])
            ->findOrFail($id_pesan);

        $totalPesanan = 0;
        foreach ($pesanan->pesananItem as $item) {
            $totalFinishing = collect($item->pesananItemFinishing)->sum('harga_finishing_snapshot');
            $totalPesanan += (($item->harga_satuan_snapshot + $totalFinishing) * $item->jumlah) + $item->harga_pengerjaan_snapshot;
        }

        $kodeUnik = PesanService::generateKodeUnik($pesanan->id_pesan);

        $totalDibayar = $pesanan->pembayaran->where('status_pembayaran', 'berhasil')->sum('nominal_bayar');

        // Perhitungkan Diskon Voucher di Grand Total
        $grandTotal = $totalPesanan + $pesanan->harga_ongkir + $kodeUnik - $pesanan->diskon_voucher_nominal;
        $sisaTagihan = max(0, $grandTotal - $totalDibayar);

        return Inertia::render('Pesan/Detail', [
            'pesanan' => $pesanan,
            'total_tagihan' => $totalPesanan,
            'kode_unik' => $kodeUnik,
            'total_transfer' => $grandTotal,
            'total_dibayar' => $totalDibayar,
            'sisa_tagihan' => $sisaTagihan,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_customer' => 'required|exists:customer,id_customer',
            'id_alamat' => 'required|string',
            'status_pembayaran' => 'required|in:belum_lunas,dibayar_sebagian,lunas',
            'nominal_bayar' => 'nullable|numeric|min:0',

            'kode_voucher' => 'nullable|string',
            'diskon_voucher_nominal' => 'nullable|numeric|min:0',

            'ekspedisi_nama' => 'required|string',
            'ekspedisi_layanan' => 'nullable|string',
            'harga_ongkir' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.id_sku' => 'required|exists:produk_sku,id_sku',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.nama_produk_snapshot' => 'required|string',
            'items.*.harga_satuan_snapshot' => 'required|numeric',

            'items.*.harga_dasar_awal_snapshot' => 'nullable|numeric',
            'items.*.total_diskon_snapshot' => 'nullable|numeric',
            'items.*.file_desain' => 'nullable|array',
            'items.*.file_desain.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:204800',
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

                'kode_voucher' => $request->kode_voucher,
                'diskon_voucher_nominal' => $request->diskon_voucher_nominal ?? 0,

                'ekspedisi_nama' => $request->ekspedisi_nama,
                'ekspedisi_layanan' => $request->ekspedisi_layanan,
                'harga_ongkir' => $request->harga_ongkir ?? 0,
            ]);

            foreach ($request->items as $index => $item) {
                $finishing = isset($item['finishing']) ? json_decode($item['finishing'], true) : [];
                $selectedFinishingIds = [];

                if (!empty($finishing) && is_array($finishing)) {
                    foreach ($finishing as $fin) {
                        $skuFin = SkuFinishing::find($fin['id_sku_finishing']);
                        if ($skuFin && $skuFin->id_pilihan_finishing) {
                            $selectedFinishingIds[] = $skuFin->id_pilihan_finishing;
                        }
                    }
                }

                $totalBeratItem = PesanService::hitungBeratTotalItem($item['id_sku'], $item['jumlah'], $selectedFinishingIds);

                $hppSatuan = Komposisi::where('id_sku', $item['id_sku'])
                    ->whereNull('id_pilihan_finishing')
                    ->sum('hpp');

                $filePaths = [];
                if ($request->hasFile("items.{$index}.file_desain")) {
                    $files = $request->file("items.{$index}.file_desain");

                    foreach ($files as $fIndex => $file) {
                        $suffix = sprintf('%03d', $fIndex + 1);
                        $filename = "{$id_pesan}-{$suffix}." . $file->getClientOriginalExtension();
                        $filePaths[] = $file->storeAs('desain_pesanan', $filename, 'public');
                    }
                }

                $rincianDiskonArray = null;
                if (isset($item['rincian_diskon_snapshot']) && !empty($item['rincian_diskon_snapshot'])) {
                    $rincianDiskonArray = is_string($item['rincian_diskon_snapshot'])
                                          ? json_decode($item['rincian_diskon_snapshot'], true)
                                          : $item['rincian_diskon_snapshot'];
                }

                $pesananItem = PesananItem::create([
                    'id_pesan' => $id_pesan,
                    'id_sku' => $item['id_sku'],
                    'nama_produk_snapshot' => $item['nama_produk_snapshot'],
                    'jumlah' => $item['jumlah'],

                    'harga_dasar_awal_snapshot' => $item['harga_dasar_awal_snapshot'] ?? $item['harga_satuan_snapshot'],
                    'total_diskon_snapshot' => $item['total_diskon_snapshot'] ?? 0,
                    'rincian_diskon_snapshot' => $rincianDiskonArray,

                    'harga_satuan_snapshot' => $item['harga_satuan_snapshot'],
                    'hpp_satuan_snapshot' => $hppSatuan,
                    'estimasi_pengerjaan_snapshot' => $item['estimasi_pengerjaan'] ?? 'Reguler',
                    'harga_pengerjaan_snapshot' => $item['harga_pengerjaan_snapshot'] ?? 0,
                    'total_berat_snapshot' => $totalBeratItem,
                    'file_desain' => !empty($filePaths) ? $filePaths : null,
                    'catatan' => $item['catatan'] ?? null,
                ]);

                if (!empty($finishing) && is_array($finishing)) {
                    foreach ($finishing as $fin) {
                        $skuFinishingAsli = SkuFinishing::find($fin['id_sku_finishing']);
                        $idPilihanFinishing = $skuFinishingAsli
                            ? $skuFinishingAsli->id_pilihan_finishing
                            : $fin['id_sku_finishing'];

                        $hppFinishing = Komposisi::where('id_sku', $item['id_sku'])
                            ->where('id_pilihan_finishing', $idPilihanFinishing)
                            ->sum('hpp');

                        PesananItemFinishing::create([
                            'id_pesanan_item' => $pesananItem->id,
                            'id_sku_finishing' => $fin['id_sku_finishing'],
                            'nama_finishing_snapshot' => $fin['nama_finishing_snapshot'],
                            'harga_finishing_snapshot' => $fin['harga_finishing_snapshot'],
                            'hpp_finishing_snapshot' => $hppFinishing,
                        ]);
                    }
                }
            }

            PembayaranService::catatPembayaranKasir(
                $pesanan,
                $request->status_pembayaran,
                $request->nominal_bayar,
                auth()->user()?->staf?->id_staf
            );

            DB::commit();

            return redirect()->route('pesan.index')->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat pesanan: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function updateOperasional(Request $request, $id_pesan)
    {
        $request->validate([
            'status_operasional' => 'required|in:keranjang,menunggu_diproses,proses_pengerjaan,proses_pengantaran,selesai,batal'
        ]);

        $pesanan = Pesan::with([
            'pesananItem.pesananItemFinishing.skuFinishing',
            'customer.user'
        ])->where(
            'id_pesan',
            $id_pesan
        )->firstOrFail();

        $statusLama = $pesanan->status_operasional;
        $statusBaru = $request->status_operasional;

        try {
            DB::beginTransaction();

            if ($statusBaru === 'proses_pengerjaan' && $statusLama !== 'proses_pengerjaan') {

                $items = $pesanan->pesananItem;
                $totalHppPesanan = 0;

                foreach ($items as $item) {
                    $totalHppPesanan += ((float) $item->hpp_satuan_snapshot * $item->jumlah);

                    $finishingTerpilih = collect($item->pesananItemFinishing ?? [])
                        ->map(fn($f) => $f->skuFinishing->id_pilihan_finishing ?? null)
                        ->filter()
                        ->toArray();

                    foreach ($item->pesananItemFinishing ?? [] as $finishing) {
                        $totalHppPesanan += ((float) $finishing->hpp_finishing_snapshot * $item->jumlah);
                    }

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

                // if ($totalHppPesanan > 0) {
                //     BukuBesarController::catatHppPenjualan($pesanan->id_pesan, $totalHppPesanan);
                // }
            }

            if ($statusBaru === 'proses_pengantaran' && $statusLama !== 'proses_pengantaran') {
                $ekspedisiDipilih = strtolower($pesanan->ekspedisi_nama ?? '');

                if ($ekspedisiDipilih === 'kurir toko') {
                    $pesanan->nomor_resi = 'LOKAL-' . date('ymd') . '-' . strtoupper(Str::random(4));
                }
            }

            if ($statusBaru === 'batal') {
                $pesanan->tanggal_selesai = null;
            }

            if ($statusBaru === 'selesai') {
                $pesanan->tanggal_selesai = now();
            } elseif ($statusBaru !== 'batal') {
                $pesanan->tanggal_selesai = null;
            }

            $pesanan->status_operasional = $statusBaru;
            $pesanan->save();

            DB::commit();
            if (!in_array($statusBaru, ['keranjang','menunggu_diproses']))
            {
                $pesanan->load('customer.user');

                PesanService::kirimNotifikasiStatus(
                    $pesanan,
                    $statusBaru
                );
            }
            return back()->with('success', 'Status Operasional berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error(
                'Update Operasional Gagal',
                [
                    'id_pesan' => $id_pesan,
                    'status' => $request->status_operasional,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return back()->with('error', 'Gagal update operasional: ' . $e->getMessage());
        }
    }

    public function updateResi(Request $request, $id_pesan)
    {
        $request->validate([
            'nomor_resi' => 'required|string|max:50'
        ]);

        $pesanan = Pesan::findOrFail($id_pesan);
        $pesanan->nomor_resi = strtoupper($request->nomor_resi);
        $pesanan->save();

        return back()->with('success', 'Nomor Resi berhasil disimpan!');
    }

    public function cetakLabel($id_pesan)
    {
        $pesanan = Pesan::with([
            'pesananItem.pesananItemFinishing.skuFinishing',
            'customer.user',
            'alamat'
        ])->where('id_pesan', $id_pesan)->firstOrFail();

        return inertia('Pesan/CetakLabel', [
            'pesanan' => $pesanan
        ]);
    }
}
