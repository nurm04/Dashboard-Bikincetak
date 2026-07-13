<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pesan;
use App\Models\Customer;
use App\Models\Pembayaran;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Staf;
use App\Models\Akun;
use App\Models\PembelianBahan;
use Illuminate\Pagination\LengthAwarePaginator; // <-- WAJIB IMPORT INI

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $key = $request->query('key');
        $sort = $request->query('sort', 'desc');

        if (empty($key)) {
            return inertia('Search/Index', [
                'results' => ['data' => [], 'total' => 0], 
                'keyword' => '',
                'sort' => $sort
            ]);
        }

        $moduls = DB::table('modul')->pluck('slug', 'nama_modul');

        $results = collect();
        $limit = 5;

        // CUSTOMER
        $customers = Customer::with('user')
            ->where('id_customer', 'like', "%{$key}%")
            ->orWhereHas('user', function($q) use ($key) { $q->where('name', 'like', "%{$key}%"); })
            ->limit($limit)->get();
        foreach ($customers as $item) {
            $results->push(['id_data' => $item->id_customer, 'keterangan' => $item->user->name ?? '-', 'nama_modul' => 'Customer', 'slug' => $moduls['Customer'] ?? 'customer', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // PESAN
        $pesans = Pesan::with(['customer.user','pesananItem'])
            ->where('id_pesan', 'like', "%{$key}%")
            ->orWhere('kode_voucher', 'like', "%{$key}%")
            ->orWhere('ekspedisi_nama', 'like', "%{$key}%")
            ->orWhere('ekspedisi_layanan', 'like', "%{$key}%")
            ->orWhere('nomor_resi', 'like', "%{$key}%")
            ->orWhereHas('customer.user', function($qUser) use ($key) {
                $qUser->where('name', 'like', "%{$key}%")
                      ->orWhere('email', 'like', "%{$key}%");
            })
            ->orWhereHas('pesananItem', function($qPesananItem) use ($key) {
                $qPesananItem->where('id_sku', 'like', "%{$key}%")
                             ->orWhere('nama_produk_snapshot', 'like', "%{$key}%");
            })
            ->whereNotIn('status_operasional', ['keranjang'])
            ->limit($limit)->get();
        foreach ($pesans as $item) {
            $results->push(['id_data' => $item->id_pesan, 'keterangan' => 'Pemesan: ' . ($item->customer->user->name ?? 'Umum'), 'nama_modul' => 'Pesan', 'slug' => $moduls['Pesan'] ?? 'pesan', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // PEMBAYARAN
        $pembayarans = Pembayaran::with('pesan.customer.user')
            ->where('id_pembayaran', 'like', "%{$key}%")
            ->orWhereHas('pesan', function($qPesan) use ($key) {
                $qPesan->where('id_pesan', 'like', "%{$key}%")
                       ->orWhereHas('customer', function($qCust) use ($key) {
                            $qCust->where('id_customer', 'like', "%{$key}%")
                                  ->orWhere('no_hp', 'like', "%{$key}%")
                                  ->orWhereHas('user', function($qUser) use ($key) {
                                        $qUser->where('name', 'like', "%{$key}%")
                                        ->orWhere('email', 'like', "%{$key}%");
                                  });
                       });
            })
            ->limit($limit)->get();
        foreach ($pembayarans as $item) {
            $results->push(['id_data' => $item->id_pembayaran, 'keterangan' => 'Rp ' . number_format($item->nominal_bayar, 0, ',', '.'), 'nama_modul' => 'Pembayaran', 'slug' => $moduls['Pembayaran'] ?? 'pembayaran', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // BAHAN BAKU
        $bahan = BahanBaku::where('id_bahan_baku', 'like', "%{$key}%")
            ->orWhere('nama_bahan_baku', 'like', "%{$key}%")
            ->limit($limit)->get();
        foreach ($bahan as $item) {
            $results->push(['id_data' => $item->id_bahan_baku, 'keterangan' => $item->nama_bahan_baku, 'nama_modul' => 'Bahan Baku', 'slug' => $moduls['Bahan Baku'] ?? 'bahan-baku', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // PEMBELIAN BAHAN
        $pembelianBahan = PembelianBahan::with('detailPembelian.bahanBaku')
            ->where('id_pembelian', 'like', "%{$key}%")
            ->orWhere('nama_supplier', 'like', "%{$key}%")
            ->orWhereHas('detailPembelian', function($qItem) use ($key) {
                      $qItem->whereHas('bahanBaku', function($qBahan) use ($key) {
                          $qBahan->where('nama_bahan_baku', 'like', "%{$key}%")
                                 ->orWhere('id_bahan_baku', 'like', "%{$key}%");
                      });
            })
            ->limit($limit)->get();
        foreach ($pembelianBahan as $item) {
            $results->push(['id_data' => $item->id_pembelian, 'keterangan' => 'Supplier: ' . $item->nama_supplier, 'nama_modul' => 'Pembelian Bahan', 'slug' => $moduls['Pembelian Bahan'] ?? 'pembelian-bahan', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // PRODUK
        $produk = Produk::where('id_produk', 'like', "%{$key}%")
            ->orWhere('nama_produk', 'like', "%{$key}%")
            ->limit($limit)->get();
        foreach ($produk as $item) {
            $results->push(['id_data' => $item->id_produk, 'keterangan' => $item->nama_produk, 'nama_modul' => 'Produk', 'slug' => $moduls['Produk'] ?? 'produk', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // STAF
        $staf = Staf::with('user')
            ->where('id_staf', 'like', "%{$key}%")
            ->orWhereHas('user', function($q) use ($key) { $q->where('name', 'like', "%{$key}%"); })
            ->limit($limit)->get();
        foreach ($staf as $item) {
            $results->push(['id_data' => $item->id_staf, 'keterangan' => $item->user->name ?? '-', 'nama_modul' => 'Staf', 'slug' => $moduls['Staf'] ?? 'staf', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // AKUN
        $akun = Akun::where('id_akun', 'like', "%{$key}%")
            ->orWhere('nama_akun', 'like', "%{$key}%")
            ->limit($limit)->get();
        foreach ($akun as $item) {
            $results->push(['id_data' => $item->id_akun, 'keterangan' => $item->nama_akun, 'nama_modul' => 'Akun', 'slug' => $moduls['Akun'] ?? 'akun', 'updated_at' => $item->updated_at->format('Y-m-d H:i')]);
        }

        // LOGIKA SORTING FILTER (Terbaru / Terlama)
        if ($sort === 'asc') {
            $sortedResults = $results->sortBy('updated_at')->values();
        } else {
            $sortedResults = $results->sortByDesc('updated_at')->values();
        }

        // LOGIKA PAGINATION MANUAL UNTUK COLLECTION (Max 16)
        $page = $request->input('page', 1);
        $perPage = 16;
        $paginatedResults = new LengthAwarePaginator(
            $sortedResults->forPage($page, $perPage)->values(),
            $sortedResults->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return inertia('Search/Index', [
            'results' => $paginatedResults,
            'keyword' => $key,
            'sort' => $sort
        ]);
    }
}