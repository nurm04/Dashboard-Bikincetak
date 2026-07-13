<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\BukuBesar;
use App\Models\Pesan;
use App\Services\BukuBesarService;
use App\Services\PesanService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        $startMonth = $request->input('start_month', Carbon::now()->format('Y-m'));
        $endMonth = $request->input('end_month', Carbon::now()->format('Y-m'));

        $startDate = Carbon::parse($startMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($endMonth . '-01')->endOfMonth();

        $dataJurnal = DB::table('buku_besar')
            ->join('akun', 'buku_besar.id_akun', '=', 'akun.id_akun')
            ->selectRaw('DATE(tanggal_transaksi) as tanggal, SUM(debit) as uang_masuk, SUM(kredit) as uang_keluar')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->where('akun.nama_akun', 'LIKE', '%Kas%')
            ->groupBy('tanggal')
            ->get();

        $labels = []; $dataPemasukan = []; $dataPengeluaran = [];
        $kumulatifPemasukan = 0; $kumulatifPengeluaran = 0;
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $tglStr = $date->format('Y-m-d');
            $labels[] = $date->isoFormat('D MMM');
            $jurnalHariIni = $dataJurnal->where('tanggal', $tglStr)->first();

            $kumulatifPemasukan += $jurnalHariIni ? (float) $jurnalHariIni->uang_masuk : 0;
            $kumulatifPengeluaran += $jurnalHariIni ? (float) $jurnalHariIni->uang_keluar : 0;

            $dataPemasukan[] = $kumulatifPemasukan;
            $dataPengeluaran[] = $kumulatifPengeluaran;
        }

        $labelStart = $startDate->isoFormat('MMMM Y');
        $labelEnd = $endDate->isoFormat('MMMM Y');
        $judulPeriode = $labelStart === $labelEnd ? $labelStart : "$labelStart s/d $labelEnd";

        $hariIni = Carbon::today();

        $pesananHariIni = Pesan::with(['pesananItem.pesananItemFinishing', 'pembayaran'])
            ->whereDate('tanggal_pesan', $hariIni)
            ->whereNotIn('status_operasional', ['keranjang', 'batal'])
            ->get();

        $omzetHariIni = 0;

        foreach ($pesananHariIni as $psn) {
            $omzetHariIni += $psn->pembayaran
                ->where('status_pembayaran', 'berhasil')
                ->sum('nominal_bayar');
        }

        $kpi = [
            'pesanan_hari_ini' => $pesananHariIni->count(),
            'omzet_hari_ini' => $omzetHariIni,
            'antrean_produksi' => Pesan::where('status_operasional', 'proses_pengerjaan')->count(),
            'siap_kirim' => Pesan::where('status_operasional', 'proses_pengantaran')->count(),
        ];

        $pesananBaru = Pesan::with(['customer.user', 'alamat', 'pesananItem.pesananItemFinishing', 'pembayaran'])
            ->where('status_operasional', 'menunggu_diproses')
            ->orderBy('tanggal_pesan', 'asc')
            ->take(5)
            ->get()
            ->map(function ($pesan) {
                $pesan->total_tagihan = PesanService::hitungTotalPesanan($pesan);
                return $pesan;
            });

        $urgentProduksi = Pesan::with(['customer.user'])
            ->where('status_operasional', 'proses_pengerjaan')
            ->orderBy('tanggal_pesan', 'asc')
            ->take(5)
            ->get();

        $topProduk = DB::table('pesanan_item')
            ->join('pesan', 'pesanan_item.id_pesan', '=', 'pesan.id_pesan')
            ->select('pesanan_item.nama_produk_snapshot', DB::raw('SUM(pesanan_item.jumlah) as total_terjual'))
            ->whereMonth('pesan.tanggal_pesan', Carbon::now()->month)
            ->whereYear('pesan.tanggal_pesan', Carbon::now()->year)
            ->where('pesan.status_operasional', '!=', 'batal')
            ->groupBy('pesanan_item.nama_produk_snapshot')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();

        $stokMenipis = DB::table('bahan_baku')
            ->where('is_active', true)
            ->where('stok_sekarang', '<=', 10)
            ->orderBy('stok_sekarang', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'grafikBEP' => [
                'labels' => $labels,
                'pemasukan' => $dataPemasukan,
                'pengeluaran' => $dataPengeluaran,
            ],
            'bulanTahun' => $judulPeriode,
            'filters' => [
                'start_month' => $startMonth,
                'end_month' => $endMonth,
            ],
            'kpi' => $kpi,
            'pesananBaru' => $pesananBaru,
            'urgentProduksi' => $urgentProduksi,
            'topProduk' => $topProduk,
            'stokMenipis' => $stokMenipis,
        ]);
    }

    public function detail(Request $request)
    {
        $startMonth = $request->input('start_month', Carbon::now()->format('Y-m'));
        $endMonth = $request->input('end_month', Carbon::now()->format('Y-m'));

        $startDate = Carbon::parse($startMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($endMonth . '-01')->endOfMonth();

        $bukuBesar = DB::table('buku_besar')
            ->join('akun', 'buku_besar.id_akun', '=', 'akun.id_akun')
            ->select(
                'buku_besar.id_buku_besar',
                'buku_besar.tanggal_transaksi',
                'akun.nama_akun',
                'buku_besar.id_referensi',
                'buku_besar.tipe_referensi',
                'buku_besar.keterangan',
                'buku_besar.debit',
                'buku_besar.kredit'
            )
            ->whereBetween('buku_besar.tanggal_transaksi', [$startDate, $endDate])
            ->orderBy('buku_besar.tanggal_transaksi', 'asc')
            ->orderBy('buku_besar.created_at', 'asc')
            ->get();

        return Inertia::render('BukuBesar/Index', [
            'bukuBesar' => $bukuBesar,
            'filters' => [
                'start_month' => $startMonth,
                'end_month' => $endMonth,
            ]
        ]);
    }

    public static function getAkunId($nama_akun)
    {
        $akun = Akun::where('nama_akun', $nama_akun)->first();
        return $akun ? $akun->id_akun : null;
    }

    public static function catatJurnal($id_akun, $id_referensi, $tipe_referensi, $keterangan, $debit, $kredit)
    {
        if (!$id_akun) return null;

        $id_buku_besar = (new BukuBesarService())->generateId();

        return BukuBesar::create([
            'id_buku_besar'     => $id_buku_besar,
            'id_akun'           => $id_akun,
            'tanggal_transaksi' => now(),
            'id_referensi'      => $id_referensi,
            'tipe_referensi'    => $tipe_referensi,
            'keterangan'        => $keterangan,
            'debit'             => $debit,
            'kredit'            => $kredit,
        ]);
    }

    public static function catatHppPenjualan($id_pesan, $total_hpp)
    {
        if ($total_hpp <= 0) return;
        $akun_hpp = self::getAkunId('Harga Pokok Produksi (HPP)');
        $akun_persediaan = self::getAkunId('Persediaan Bahan Baku');

        self::catatJurnal(
            $akun_hpp,
            $id_pesan,
            'penjualan',
            "HPP Bahan Baku Pesanan #$id_pesan",
            $total_hpp,
            0
        );

        self::catatJurnal(
            $akun_persediaan,
            $id_pesan,
            'penjualan',
            "Pengurangan Stok untuk Pesanan #$id_pesan",
            0,
            $total_hpp
        );
    }
}
