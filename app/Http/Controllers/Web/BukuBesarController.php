<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\BukuBesar;
use App\Services\BukuBesarService;
use Carbon\Carbon;
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
            'Pendapatan',
            "HPP Bahan Baku Pesanan #$id_pesan",
            $total_hpp,
            0
        );

        self::catatJurnal(
            $akun_persediaan,
            $id_pesan,
            'Pendapatan',
            "Pengurangan Stok untuk Pesanan #$id_pesan",
            0,
            $total_hpp
        );
    }
}
