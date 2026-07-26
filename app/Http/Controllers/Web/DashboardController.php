<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Pembayaran; // Tambahan import model Pembayaran
use App\Models\Pesan;
use App\Models\PesananItemProduksi;
use App\Services\PesanService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'staf') {

            $spesifikRole = DB::table('staf')
                ->join('role_staf', 'staf.id_role_staf', '=', 'role_staf.id_role_staf')
                ->where('staf.user_id', $user->id)
                ->value('role_staf.role');

            $spesifikRole = strtolower($spesifikRole ?? '');

            if (in_array($spesifikRole, ['admin', 'administrator'])) {
                $data = $this->getAdminData($request);
            } elseif ($spesifikRole === 'kasir') {
                $data = $this->getKasirData($request);
            } else {
                $data = $this->getProduksiData();
            }

            return Inertia::render('Dashboard/Index', $data);

        }

        abort(403, 'Akses Dashboard Utama hanya untuk Staf Internal.');
    }

    private function getAdminData(Request $request)
    {
        $startMonth = $request->input('start_month', Carbon::now()->format('Y-m'));
        $endMonth = $request->input('end_month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($startMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($endMonth . '-01')->endOfMonth();

        $dataJurnal = DB::table('buku_besar')->join('akun', 'buku_besar.id_akun', '=', 'akun.id_akun')
            ->selectRaw('DATE(tanggal_transaksi) as tanggal, SUM(debit) as uang_masuk, SUM(kredit) as uang_keluar')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->where('akun.nama_akun', 'LIKE', '%Kas%')->groupBy('tanggal')->get();

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

        $hariIni = Carbon::today();

        // Optimasi: Langsung hitung jumlah tanpa me-load data relasi
        $pesananHariIniCount = Pesan::whereDate('tanggal_pesan', $hariIni)
            ->whereNotIn('status_operasional', ['keranjang', 'batal'])->count();

        // Mengambil omzet dari total pembayaran hari ini yang berhasil
        // Catatan: Ubah 'updated_at' menjadi 'created_at' atau 'tanggal_bayar' jika skema database Anda berbeda
        $omzetHariIni = Pembayaran::whereDate('updated_at', $hariIni)
            ->where('status_pembayaran', 'berhasil')
            ->sum('nominal_bayar');

        return [
            'role_view' => 'admin',
            'grafikBEP' => ['labels' => $labels, 'pemasukan' => $dataPemasukan, 'pengeluaran' => $dataPengeluaran],
            'filters' => ['start_month' => $startMonth, 'end_month' => $endMonth],
            'kpi' => [
                'pesanan_hari_ini' => $pesananHariIniCount,
                'omzet_hari_ini' => $omzetHariIni,
                'antrean_produksi' => Pesan::where('status_operasional', 'menunggu_diproses')->has('pembayaran')->count(),
                'siap_kirim' => Pesan::where('status_operasional', 'proses_pengantaran')->count(),
            ],
            'pesananBaru' => $this->getPesananBaru(),
            'urgentProduksi' => $this->getUrgentProduksi(),
            'stokMenipis' => $this->getStokMenipis(),
            'topProduk' => DB::table('pesanan_item')->join('pesan', 'pesanan_item.id_pesan', '=', 'pesan.id_pesan')
                ->select('pesanan_item.nama_produk_snapshot', DB::raw('SUM(pesanan_item.jumlah) as total_terjual'))
                ->whereMonth('pesan.tanggal_pesan', Carbon::now()->month)->where('pesan.status_operasional', '!=', 'batal')
                ->groupBy('pesanan_item.nama_produk_snapshot')->orderByDesc('total_terjual')->take(5)->get()
        ];
    }

    private function getKasirData(Request $request)
    {
        $hariIni = Carbon::today();

        // Optimasi: Langsung hitung jumlah tanpa me-load data relasi
        $pesananHariIniCount = Pesan::whereDate('tanggal_pesan', $hariIni)
            ->whereNotIn('status_operasional', ['keranjang', 'batal'])->count();

        // Mengambil omzet dari total pembayaran hari ini yang berhasil
        $omzetHariIni = Pembayaran::whereDate('updated_at', $hariIni)
            ->where('status_pembayaran', 'berhasil')
            ->sum('nominal_bayar');

        $tagihanVendorPending = PesananItemProduksi::with(['vendor', 'pesananItem.pesan'])
            ->whereNotNull('id_vendor')
            ->whereNull('id_tagihan_vendor')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $item->nama_vendor = $item->vendor?->nama_vendor;
                $item->kode_transaksi = $item->pesananItem?->pesan?->kode_transaksi;
                $item->id_pesan = $item->pesananItem?->pesan?->id_pesan;

                return $item;
            });

        $tagihanPendingCount = PesananItemProduksi::whereNotNull('id_vendor')
            ->whereNull('id_tagihan_vendor')
            ->count();

        return [
            'role_view' => 'kasir',
            'kpi' => [
                'pesanan_hari_ini' => $pesananHariIniCount,
                'omzet_hari_ini' => $omzetHariIni,
                'tagihan_pending' => $tagihanPendingCount,
            ],
            'pesananBaru' => $this->getPesananBaru(),
            'tagihanVendorPending' => $tagihanVendorPending
        ];
    }

    private function getProduksiData()
    {
        return [
            'role_view' => 'produksi',
            'kpi' => [
                'antrean_produksi' => Pesan::where('status_operasional', 'menunggu_diproses')->has('pembayaran')->count(),
                'siap_kirim' => Pesan::where('status_operasional', 'proses_pengantaran')->count(),
            ],
            'urgentProduksi' => $this->getUrgentProduksi(),
            'stokMenipis' => $this->getStokMenipis(),
        ];
    }

    private function getPesananBaru() {
        return Pesan::with(['customer.user', 'alamat'])->where('status_operasional', 'menunggu_diproses')
            ->orderBy('tanggal_pesan', 'asc')->take(5)->get()->map(function ($pesan) {
                $pesan->total_tagihan = PesanService::hitungTotalPesanan($pesan);
                return $pesan;
            });
    }

    private function getUrgentProduksi() {
        return Pesan::with(['customer.user'])
            ->where('status_operasional', 'menunggu_diproses')
            ->has('pembayaran')
            ->orderBy('tanggal_pesan', 'asc')
            ->take(5)
            ->get();
    }

    private function getStokMenipis() {
        return BahanBaku::where('is_active', true)->where('stok_sekarang', '<=', 10)
            ->orderBy('stok_sekarang', 'asc')->take(5)->get();
    }
}
