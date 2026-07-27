<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Customer;
use App\Models\Komposisi;
use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemFinishing;
use App\Models\SkuFinishing;
use App\Models\Voucher;
use App\Services\PembayaranService;
use App\Services\PesanService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
                'customer.alamat',
                'pesananItem.pesananItemFinishing',
                'alamat',
                'pembayaran'
            ])
            ->findOrFail($id_pesan);

        $rincian = PesanService::kalkulasiRincianPesanan($pesanan);

        $typePembayaran = DB::select("SHOW COLUMNS FROM pesan WHERE Field = 'status_pembayaran'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typePembayaran, $matchesPembayaran);
        $enumPembayaran = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesPembayaran[1]));

        $typeOperasional = DB::select("SHOW COLUMNS FROM pesan WHERE Field = 'status_operasional'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeOperasional, $matchesOperasional);
        $enumOperasional = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesOperasional[1]));

        return Inertia::render('Pesan/Detail', [
            'pesanan'         => $pesanan,
            'total_tagihan'   => $rincian['subtotal'],
            'kode_unik'       => $rincian['kode_unik'],
            'total_transfer'  => $rincian['grand_total'],
            'total_dibayar'   => $rincian['total_dibayar'],
            'sisa_tagihan'    => $rincian['sisa_tagihan'],
            'enumPembayaran'  => $enumPembayaran,
            'enumOperasional' => $enumOperasional,
        ]);
    }

    public function posKasir()
    {
        $customers = Customer::with(['user', 'alamat', 'roleCustomer'])->get();
        $vouchers = Voucher::where('is_active', true)
            ->where('berlaku_dari', '<=', now())
            ->where('berlaku_sampai', '>=', now())
            ->get();

        $typePembayaran = DB::select("SHOW COLUMNS FROM pesan WHERE Field = 'status_pembayaran'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typePembayaran, $matchesPembayaran);
        $enumPembayaran = array_map(function($value){ return trim($value, "'"); }, explode(',', $matchesPembayaran[1]));

        return Inertia::render('Pesan/PosKasir', [
            'customers' => $customers,
            'vouchers' => $vouchers,
            'enumPembayaran' => $enumPembayaran,
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

            'items.*.file_desain' => 'nullable',
            'items.*.tipe_file' => 'nullable|string|in:upload,link,email',
            'items.*.link_file' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $id_pesan = PesanService::generateId();
            $kode_transaksi = PesanService::generateKodeTransaksi();

            $maxHari = 1;

            if (in_array($request->status_pembayaran, ['dibayar_sebagian', 'lunas'])) {
                foreach ($request->items as $item) {
                    $estimasi = $item['estimasi_pengerjaan'] ?? 'Reguler';
                    if (preg_match('/(\d+)/', $estimasi, $matches)) {
                        $hari = (int) $matches[1];
                        if ($hari > $maxHari) {
                            $maxHari = $hari;
                        }
                    }
                }
            }

            $waktuDeadline = in_array($request->status_pembayaran, ['dibayar_sebagian', 'lunas'])
                ? Carbon::now()->addDays($maxHari)
                : null;

            $pesanan = Pesan::create([
                'id_pesan' => $id_pesan,
                'kode_transaksi' => $kode_transaksi,
                'id_customer' => $request->id_customer,
                'id_alamat' => $request->id_alamat,
                'status_operasional' => 'menunggu_diproses',
                'status_pembayaran' => $request->status_pembayaran,
                'waktu_deadline' => $waktuDeadline,

                'kode_voucher' => $request->kode_voucher,
                'diskon_voucher_nominal' => $request->diskon_voucher_nominal ?? 0,

                'ekspedisi_nama' => $request->ekspedisi_nama,
                'ekspedisi_layanan' => $request->ekspedisi_layanan,
                'harga_ongkir' => $request->harga_ongkir ?? 0,
            ]);

            foreach ($request->items as $index => $item) {
                // 1. Cek apakah ini produk custom
                $isCustom = $item['id_sku'] === 'SKU-CUSTOM';

                $finishing = isset($item['finishing']) ? json_decode($item['finishing'], true) : [];
                $selectedFinishingIds = [];

                // 2. Skip query finishing master kalau ini produk custom
                if (!$isCustom && !empty($finishing) && is_array($finishing)) {
                    foreach ($finishing as $fin) {
                        $skuFin = SkuFinishing::find($fin['id_sku_finishing']);
                        if ($skuFin && $skuFin->id_pilihan_finishing) {
                            $selectedFinishingIds[] = $skuFin->id_pilihan_finishing;
                        }
                    }
                }

                // 3. Set berat 0 kalau custom, kalau reguler hitung dari service
                $totalBeratItem = $isCustom ? 0 : PesanService::hitungBeratTotalItem($item['id_sku'], $item['jumlah'], $selectedFinishingIds);

                // 4. Set HPP 0 kalau custom, kalau reguler hitung dari tabel Komposisi
                $hppSatuan = 0;
                if (!$isCustom) {
                    $hppSatuan = Komposisi::where('id_sku', $item['id_sku'])
                        ->whereNull('id_pilihan_finishing')
                        ->sum('hpp');
                }

                $fileDesainData = null;

                if ($request->hasFile("items.{$index}.file_desain")) {
                    $file = $request->file("items.{$index}.file_desain");
                    if (is_array($file)) $file = $file[0];

                    $filename = "{$id_pesan}-" . \Illuminate\Support\Str::random(6) . "." . $file->getClientOriginalExtension();
                    $path = $file->storeAs('desain_pesanan', $filename, 'public');

                    $fileDesainData = [
                        'tipe' => 'upload',
                        'nilai' => $path
                    ];
                } elseif (isset($item['tipe_file']) && $item['tipe_file'] === 'link') {
                    $fileDesainData = [
                        'tipe' => 'link',
                        'nilai' => $item['link_file'] ?? ''
                    ];
                } elseif (isset($item['tipe_file']) && $item['tipe_file'] === 'email') {
                    $fileDesainData = [
                        'tipe' => 'email',
                        'nilai' => 'Akan dikirim oleh customer melalui Email.'
                    ];
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
                    'file_desain' => $fileDesainData,
                    'catatan' => $item['catatan'] ?? null,
                ]);

                // 5. Bypass (skip) insert ke tabel pesanan_item_finishing kalau ini produk custom
                if (!$isCustom && !empty($finishing) && is_array($finishing)) {
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

    public function cetakLabelItem($id)
    {
        $item = PesananItem::with([
            'pesananItemFinishing',
            'pesan.customer.user',
            'pesan.alamat'
        ])->findOrFail($id);

        return inertia('Pesan/CetakLabelItem', [
            'item' => $item
        ]);
    }

    private function cekBisaDiedit(Pesan $pesanan)
    {
        $statusDilarang = ['proses_pengerjaan', 'proses_pengantaran', 'selesai', 'batal'];

        if (in_array($pesanan->status_operasional, $statusDilarang)) {
            throw new HttpResponseException(
                redirect()->back()->with('error', 'Pesanan sudah masuk tahap pengerjaan/selesai dan tidak dapat diubah lagi.')
            );
        }
    }

    public function updateAlamat(Request $request, $id_pesan)
    {
        $request->validate([
            'id_alamat' => 'required|exists:alamat,id_alamat',
            'ekspedisi_nama' => 'required|string',
            'ekspedisi_layanan' => 'required|string',
            'harga_ongkir' => 'required|numeric'
        ]);

        $pesanan = Pesan::findOrFail($id_pesan);

        $this->cekBisaDiedit($pesanan);

        DB::beginTransaction();
        try {
            $pesanan->update([
                'id_alamat' => $request->id_alamat,
                'ekspedisi_nama' => strtoupper($request->ekspedisi_nama),
                'ekspedisi_layanan' => $request->ekspedisi_layanan,
                'harga_ongkir' => $request->harga_ongkir
            ]);

            $this->sinkronisasiKeuanganPesanan($id_pesan);

            DB::commit();
            return back()->with('success', 'Alamat & Pengiriman berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal ganti alamat/ekspedisi pesanan: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui alamat pengiriman.');
        }
    }

    public function addItem(Request $request)
    {
        $pesanan = Pesan::findOrFail($request->id_pesan);

        $this->cekBisaDiedit($pesanan);

        DB::beginTransaction();
        try {
            $item = $this->saveItem($request);
            $this->sinkronisasiKeuanganPesanan($request->id_pesan);

            DB::commit();
            return back()->with('success', 'Item berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal tambah item: '.$e->getMessage());
            return back()->with('error', 'Gagal tambah item: '.$e->getMessage());
        }
    }
    public function updateItem(Request $request, $id)
    {
        $item = PesananItem::findOrFail($id);
        $pesanan = Pesan::findOrFail($item->id_pesan);

        $this->cekBisaDiedit($pesanan);

        DB::beginTransaction();
        try {
            $this->saveItem($request, $item);
            $this->sinkronisasiKeuanganPesanan($pesanan->id_pesan);

            DB::commit();
            return back()->with('success', 'Item berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update item: '.$e->getMessage());
            return back()->with('error', 'Gagal update item: '.$e->getMessage());
        }
    }
    public function deleteItem($id)
    {
        $item = PesananItem::findOrFail($id);
        $pesanan = Pesan::findOrFail($item->id_pesan);

        $this->cekBisaDiedit($pesanan);

        DB::beginTransaction();
        try {
            $id_pesan = $item->id_pesan;

            $item->pesananItemFinishing()->delete();
            $item->delete();

            $this->sinkronisasiKeuanganPesanan($id_pesan);

            DB::commit();
            return back()->with('success', 'Item berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with(
                'error',
                'Gagal menghapus item.'
            );
        }
    }

    private function saveItem(Request $request, PesananItem $item = null)
    {
        $request->validate([
            'id_pesan' => 'required|exists:pesan,id_pesan',
            'id_sku' => 'required|exists:produk_sku,id_sku',
            'jumlah' => 'required|numeric|min:1',
            'harga_satuan_snapshot' => 'required|numeric',
            'harga_dasar_awal_snapshot' => 'nullable|numeric',
            'total_diskon_snapshot' => 'nullable|numeric',
            'estimasi_pengerjaan' => 'required',
            'harga_pengerjaan_snapshot' => 'nullable|numeric',
            'file' => 'nullable',
            'tipe_file' => 'nullable|string|in:upload,link,email',
            'link_file' => 'nullable|string',
        ]);

        $finishing = isset($request->finishing)
            ? json_decode($request->finishing, true)
            : [];

        $selectedFinishingIds = [];

        if (!empty($finishing)) {
            foreach ($finishing as $fin) {
                $skuFin = SkuFinishing::find($fin['id_sku_finishing']);

                if ($skuFin && $skuFin->id_pilihan_finishing) {
                    $selectedFinishingIds[] = $skuFin->id_pilihan_finishing;
                }
            }
        }

        $totalBeratItem = PesanService::hitungBeratTotalItem(
            $request->id_sku,
            $request->jumlah,
            $selectedFinishingIds
        );

        $hppSatuan = Komposisi::where('id_sku', $request->id_sku)
            ->whereNull('id_pilihan_finishing')
            ->sum('hpp');

        $fileDesainData = $item?->file_desain;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $request->id_pesan . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(
                'desain_pesanan',
                $filename,
                'public'
            );
            $fileDesainData = [
                'tipe' => 'upload',
                'nilai' => $path,
            ];
        } elseif ($request->tipe_file === 'link') {
            $fileDesainData = [
                'tipe' => 'link',
                'nilai' => $request->link_file ?? '',
            ];
        } elseif ($request->tipe_file === 'email') {
            $fileDesainData = [
                'tipe' => 'email',
                'nilai' => 'Akan dikirim oleh customer melalui Email.',
            ];
        }

        $rincianDiskonArray = null;
        if ($request->filled('rincian_diskon_snapshot')) {
            $rincianDiskonArray = is_string($request->rincian_diskon_snapshot)
                ? json_decode($request->rincian_diskon_snapshot, true)
                : $request->rincian_diskon_snapshot;
        }

        $data = [
            'id_pesan' => $request->id_pesan,
            'id_sku' => $request->id_sku,
            'nama_produk_snapshot' => $request->nama_produk_snapshot,
            'jumlah' => $request->jumlah,

            'harga_dasar_awal_snapshot' => $request->harga_dasar_awal_snapshot ?? $request->harga_satuan_snapshot,
            'total_diskon_snapshot' => $request->total_diskon_snapshot ?? 0,
            'rincian_diskon_snapshot' => $rincianDiskonArray,

            'harga_satuan_snapshot' => $request->harga_satuan_snapshot,
            'hpp_satuan_snapshot' => $hppSatuan,

            'estimasi_pengerjaan_snapshot' => $request->estimasi_pengerjaan,
            'harga_pengerjaan_snapshot' => $request->harga_pengerjaan_snapshot ?? 0,

            'total_berat_snapshot' => $totalBeratItem,
            'file_desain' => $fileDesainData,

            'catatan' => $request->catatan,
        ];

        if ($item) {
            $item->update($data);
        } else {
            $item = PesananItem::create($data);
        }

        $item->pesananItemFinishing()->delete();
        if (!empty($finishing)) {
            foreach ($finishing as $fin) {
                $skuFinishing = SkuFinishing::find($fin['id_sku_finishing']);
                $idPilihanFinishing = $skuFinishing
                    ? $skuFinishing->id_pilihan_finishing
                    : $fin['id_sku_finishing'];

                $hppFinishing = Komposisi::where('id_sku', $request->id_sku)
                    ->where('id_pilihan_finishing', $idPilihanFinishing)
                    ->sum('hpp');

                $item->pesananItemFinishing()->create([
                    'id_sku_finishing' => $fin['id_sku_finishing'],
                    'nama_finishing_snapshot' => $fin['nama_finishing_snapshot'],
                    'harga_finishing_snapshot' => $fin['harga_finishing_snapshot'],
                    'hpp_finishing_snapshot' => $hppFinishing,
                ]);
            }
        }

        return $item;
    }

    private function sinkronisasiKeuanganPesanan($id_pesan)
    {
        $pesanan = Pesan::with(['pesananItem.pesananItemFinishing', 'pembayaran'])->findOrFail($id_pesan);

        $rincian = PesanService::kalkulasiRincianPesanan($pesanan);

        $totalTagihan = $rincian['grand_total'];
        $totalDibayar = $rincian['total_dibayar'];

        if ($totalTagihan <= 0) {
            $pesanan->status_pembayaran = 'belum_lunas';
        } elseif ($totalDibayar >= $totalTagihan) {
            $pesanan->status_pembayaran = 'lunas';
        } elseif ($totalDibayar > 0) {
            $pesanan->status_pembayaran = 'dibayar_sebagian';
        } else {
            $pesanan->status_pembayaran = 'belum_lunas';
        }

        $pesanan->save();
    }
}
