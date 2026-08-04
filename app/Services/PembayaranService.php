<?php

namespace App\Services;

use App\Events\ProduksiBaruEvent;
use App\Http\Controllers\Web\BukuBesarController;
use App\Models\Pembayaran;

class PembayaranService
{
    public static function generateId(): string
    {
        $prefix = 'BYR-' . date('ymd') . '-';

        $latest = Pembayaran::where('id_pembayaran','like',$prefix . '%')
            ->orderBy('id_pembayaran', 'desc')
            ->first();

        $number = $latest ? (int) substr($latest->id_pembayaran, -4) + 1 : 1;

        return $prefix . str_pad($number,4,'0',STR_PAD_LEFT);
    }

    public static function catatPembayaranKasir($pesanan, $statusPembayaran, $nominalBayar, $idStaf = null)
    {
        if ($statusPembayaran === 'belum_lunas' || $nominalBayar <= 0) {
            return null;
        }

        $idPembayaran = self::generateId();

        $pembayaran = Pembayaran::create([
            'id_pembayaran'     => $idPembayaran,
            'id_pesan'          => $pesanan->id_pesan,
            'nominal_bayar'     => $nominalBayar,
            'metode_pembayaran' => 'kasir',
            'status_pembayaran' => 'berhasil',
            'id_staf'           => $idStaf,
        ]);

        $akunKas        = BukuBesarController::getAkunId('Kas Bank (BCA/Mandiri/dll)');
        $akunPendapatan = BukuBesarController::getAkunId('Pendapatan Jasa Percetakan');

        BukuBesarController::catatJurnal(
            $akunKas,
            $idPembayaran,
            'pendapatan',
            "Penerimaan Pembayaran Kasir Pesanan #{$pesanan->id_pesan}",
            $nominalBayar,
            0
        );

        BukuBesarController::catatJurnal(
            $akunPendapatan,
            $idPembayaran,
            'pendapatan',
            "Pendapatan Penjualan Kasir Pesanan #{$pesanan->id_pesan}",
            0,
            $nominalBayar
        );
        event(new ProduksiBaruEvent($pesanan));

        return $pembayaran;
    }
}
