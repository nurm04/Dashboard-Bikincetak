<?php

namespace App\Http\Controllers;

use App\Models\BukuBesar;
use App\Models\Akun;
use App\Services\BukuBesarService;

class BukuBesarController extends Controller
{
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
