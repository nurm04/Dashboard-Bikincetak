<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Komposisi;
use App\Models\Penawaran;
use App\Models\PenawaranItem;
use App\Models\PenawaranItemFinishing;
use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemFinishing;
use App\Services\PesanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PenawaranController extends Controller
{
    /**
     * Menampilkan daftar Surat Penawaran
     */
    public function index(Request $request)
    {
        $query = Penawaran::with([
            'customer.user',
            'penawaranItem',
            'penawaranItem.penawaranItemFinishing'
        ])->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('id_penawaran', 'like', '%' . $search . '%')
                  ->orWhereHas('customer.user', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('no_hp', 'like', '%' . $search . '%');
                  });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status_penawaran', $request->status);
        }

        $penawaran = $query->paginate(10)->withQueryString();

        return Inertia::render('Penawaran/Index', [
            'penawaran' => $penawaran,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Halaman form pembuatan Penawaran Baru (Mirip POS Kasir)
     */
    public function create()
    {
        // Cukup tarik Customer aja lengkap dengan rolenya
        $customers = Customer::with(['user', 'alamat', 'roleCustomer'])->get();

        return Inertia::render('Penawaran/Create', [
            'customers' => $customers
        ]);
    }

    /**
     * Menyimpan data Penawaran Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_customer' => 'required|exists:customer,id_customer',
            'id_alamat' => 'nullable|string',
            'berlaku_sampai' => 'nullable|date',
            'catatan_penawaran' => 'nullable|string',

            'ekspedisi_nama' => 'nullable|string',
            'ekspedisi_layanan' => 'nullable|string',
            'harga_ongkir' => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.id_sku' => 'required|exists:produk_sku,id_sku',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.nama_produk_snapshot' => 'required|string',
            'items.*.harga_satuan_snapshot' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // 1. Generate ID Penawaran format: PP-YYYYMM-XXXX
            $prefix = 'PP-' . date('Ym') . '-';
            $latest = Penawaran::where('id_penawaran', 'like', $prefix . '%')
                ->orderBy('id_penawaran', 'desc')
                ->first();
            $number = $latest ? (int) substr($latest->id_penawaran, -4) + 1 : 1;
            $id_penawaran = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

            // 2. 👇 Generate Kode Penawaran (8 Karakter Random Uppercase) 👇
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charLength = strlen($characters);

            do {
                $randomString = '';
                for ($i = 0; $i < 8; $i++) {
                    $randomString .= $characters[rand(0, $charLength - 1)];
                }
                // Pastikan kode belum pernah dipakai di tabel penawaran
                $exists = Penawaran::where('kode_penawaran', $randomString)->exists();
            } while ($exists);

            $kode_penawaran = $randomString;
            // 👆 SELESAI GENERATE KODE 👆

            $penawaran = Penawaran::create([
                'id_penawaran' => $id_penawaran,
                'kode_penawaran' => $kode_penawaran, // Masukkan kodenya ke sini
                'id_customer' => $request->id_customer,
                'id_alamat' => $request->id_alamat,

                'status_penawaran' => 'draft',
                'sumber_penawaran' => 'pos_kasir',

                'berlaku_sampai' => $request->berlaku_sampai,
                'catatan_penawaran' => $request->catatan_penawaran,

                'ekspedisi_nama' => $request->ekspedisi_nama,
                'ekspedisi_layanan' => $request->ekspedisi_layanan,
                'harga_ongkir' => $request->harga_ongkir ?? 0,
            ]);

            foreach ($request->items as $item) {
                // Parse Atribut Custom
                $atributCustomArray = [];
                if (isset($item['atribut_custom_snapshot'])) {
                    $atributCustomArray = is_string($item['atribut_custom_snapshot'])
                        ? json_decode($item['atribut_custom_snapshot'], true)
                        : $item['atribut_custom_snapshot'];
                }

                // Parse Rincian Diskon
                $rincianDiskonArray = [];
                if (isset($item['rincian_diskon_snapshot'])) {
                    $rincianDiskonArray = is_string($item['rincian_diskon_snapshot'])
                        ? json_decode($item['rincian_diskon_snapshot'], true)
                        : $item['rincian_diskon_snapshot'];
                }

                // Kalkulasi HPP Dasar
                $hppSatuan = 0;
                $komposisiDasar = Komposisi::where('id_sku', $item['id_sku'])
                    ->whereNull('id_pilihan_finishing')
                    ->get();

                foreach ($komposisiDasar as $kd) {
                    $hppSatuan += $kd->hpp;
                }

                $penawaranItem = PenawaranItem::create([
                    'id_penawaran' => $id_penawaran,
                    'id_sku' => $item['id_sku'],
                    'nama_produk_snapshot' => $item['nama_produk_snapshot'],
                    'jumlah' => $item['jumlah'],

                    'harga_dasar_awal_snapshot' => $item['harga_dasar_awal_snapshot'] ?? $item['harga_satuan_snapshot'],
                    'total_diskon_snapshot' => $item['total_diskon_snapshot'] ?? 0,

                    'rincian_diskon_snapshot' => empty($rincianDiskonArray) ? null : json_encode($rincianDiskonArray),

                    'harga_satuan_snapshot' => $item['harga_satuan_snapshot'],
                    'hpp_satuan_snapshot' => $hppSatuan,

                    'estimasi_pengerjaan_snapshot' => $item['estimasi_pengerjaan'] ?? 'Reguler',
                    'harga_pengerjaan_snapshot' => $item['harga_pengerjaan_snapshot'] ?? 0,

                    'catatan' => $item['catatan'] ?? null,
                    'atribut_custom_snapshot' => empty($atributCustomArray) ? null : json_encode($atributCustomArray)
                ]);

                // Simpan Data Finishing
                $finishing = isset($item['finishing']) && is_string($item['finishing'])
                    ? json_decode($item['finishing'], true)
                    : ($item['finishing'] ?? []);

                if (!empty($finishing) && is_array($finishing)) {
                    foreach ($finishing as $fin) {
                        PenawaranItemFinishing::create([
                            'id_penawaran_item'       => $penawaranItem->id,
                            'id_sku_finishing'        => $fin['id_sku_finishing'],
                            'nama_finishing_snapshot' => $fin['nama_finishing_snapshot'],
                            'harga_finishing_snapshot'=> $fin['harga_finishing_snapshot'],
                            'hpp_finishing_snapshot'  => 0,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('penawaran.index')
                ->with('success', 'Surat Penawaran berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat penawaran: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat penawaran. Periksa log sistem.');
        }
    }

    /**
     * Menampilkan Detail Surat Penawaran (Bisa untuk Generate PDF/Print)
     */
    public function detail($id)
    {
        $penawaran = Penawaran::with([
            'customer.user',
            'alamat',
            'penawaranItem.penawaranItemFinishing'
        ])->findOrFail($id);

        return Inertia::render('Penawaran/Detail', [
            'penawaran' => $penawaran
        ]);
    }

    /**
     * Mengubah Status Penawaran (Misal: ACC jadi pesanan atau Ditolak)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_penawaran' => 'required|in:terkirim,disetujui,ditolak,kedaluwarsa'
        ]);

        $penawaran = Penawaran::findOrFail($id);
        $penawaran->status_penawaran = $request->status_penawaran;
        $penawaran->save();

        return back()->with('success', 'Status penawaran berhasil diubah.');
    }
    /**
     * Halaman Cetak Surat Penawaran A4
     */
    public function cetak($id)
    {
        $penawaran = Penawaran::with([
            'customer.user',
            'alamat',
            'penawaranItem.penawaranItemFinishing'
        ])->findOrFail($id);

        return Inertia::render('Penawaran/CetakDokumen', [
            'penawaran' => $penawaran
        ]);
    }

    public function convertToSO($id)
    {
        $penawaran = Penawaran::with(['penawaranItem.penawaranItemFinishing'])->findOrFail($id);

        if ($penawaran->id_pesan_terkait) {
            return back()->with('error', 'Penawaran ini sudah pernah dikonversi menjadi Pesanan (SO)!');
        }

        try {
            DB::beginTransaction();
            $id_pesan = PesanService::generateId();
            $kode_transaksi = PesanService::generateKodeTransaksi();

            // Bikin Record Pesanan Utama (Dilengkapi Fallback 0/Null biar MySQL gak crash)
            $pesan = Pesan::create([
                'id_pesan' => $id_pesan,
                'kode_transaksi' => $kode_transaksi,
                'id_customer' => $penawaran->id_customer,
                'id_alamat' => $penawaran->id_alamat,

                'tanggal_pesan' => now(),
                'status_pesanan' => 'menunggu_pembayaran',
                'status_pembayaran' => 'belum_lunas',

                'ekspedisi_nama' => $penawaran->ekspedisi_nama,
                'ekspedisi_layanan' => $penawaran->ekspedisi_layanan,
                'harga_ongkir' => $penawaran->harga_ongkir ?? 0,

                'kode_voucher' => $penawaran->kode_voucher ?? null,
                'diskon_voucher_nominal' => $penawaran->diskon_voucher_nominal ?? 0,

                'catatan' => 'Hasil Konversi dari Penawaran: ' . $penawaran->id_penawaran . "\n\n" . $penawaran->catatan_penawaran,

                'total_tagihan' => 0,
                'total_dibayar' => 0,
                'sisa_tagihan' => 0,
            ]);

            $totalProduk = 0;

            // Bikin Record Item
            foreach ($penawaran->penawaranItem as $item) {

                // Query manual biar aman dari error relasi hantu
                $tipeKalkulasi = DB::table('produk_sku')->where('id_sku', $item->id_sku)->value('tipe_kalkulasi') ?? 'standard';

                $pesananItem = PesananItem::create([
                    'id_pesan' => $id_pesan,
                    'id_sku' => $item->id_sku,
                    'nama_produk_snapshot' => $item->nama_produk_snapshot,
                    'jumlah' => $item->jumlah,
                    'tipe_kalkulasi' => $tipeKalkulasi,

                    'atribut_custom_snapshot' => $item->atribut_custom_snapshot,

                    'harga_dasar_awal_snapshot' => $item->harga_dasar_awal_snapshot,
                    'total_diskon_snapshot' => $item->total_diskon_snapshot ?? 0,
                    'rincian_diskon_snapshot' => $item->rincian_diskon_snapshot,
                    'harga_satuan_snapshot' => $item->harga_satuan_snapshot,
                    'hpp_satuan_snapshot' => $item->hpp_satuan_snapshot ?? 0,

                    'estimasi_pengerjaan_snapshot' => $item->estimasi_pengerjaan_snapshot,
                    'harga_pengerjaan_snapshot' => $item->harga_pengerjaan_snapshot ?? 0,
                    'total_berat_snapshot' => $item->total_berat_snapshot ?? 0, // 👈 Ini biang keroknya kemaren (null)

                    'file_desain' => $item->file_desain ?? null,
                    'catatan' => $item->catatan,
                ]);

                $totalFinishing = 0;

                foreach ($item->penawaranItemFinishing as $fin) {
                    PesananItemFinishing::create([
                        'id_pesanan_item' => $pesananItem->id,
                        'id_sku_finishing' => $fin->id_sku_finishing,
                        'nama_finishing_snapshot' => $fin->nama_finishing_snapshot,
                        'harga_finishing_snapshot' => $fin->harga_finishing_snapshot,
                        'hpp_finishing_snapshot' => $fin->hpp_finishing_snapshot ?? 0,
                    ]);

                    $totalFinishing += ($fin->harga_finishing_snapshot * $item->jumlah);
                }

                $subtotalItem = ($item->harga_satuan_snapshot * $item->jumlah) + $totalFinishing + ($item->harga_pengerjaan_snapshot ?? 0);
                $totalProduk += $subtotalItem;
            }

            // Update Total Harga SO
            $grandTotal = $totalProduk + ($penawaran->harga_ongkir ?? 0) - ($penawaran->diskon_voucher_nominal ?? 0);

            $pesan->update([
                'total_tagihan' => $grandTotal,
                'sisa_tagihan' => $grandTotal,
            ]);

            // Tandai Penawaran Udah Punya Link ke SO
            $penawaran->update([
                'id_pesan_terkait' => $id_pesan
            ]);

            DB::commit();

            return redirect()->route('pesan.detail', $id_pesan)
                ->with('success', 'Sales Order Resmi berhasil dibuat dari Penawaran!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal convert Penawaran ke SO: ' . $e->getMessage());
            // 👇 SEKARANG ERROR ASLINYA DIKIRIM KE VUE BIAR LU TAU MASALAHNYA 👇
            return back()->with('error', 'Gagal membuat SO: ' . $e->getMessage());
        }
    }
}
