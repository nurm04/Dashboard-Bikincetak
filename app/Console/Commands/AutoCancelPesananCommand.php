<?php

namespace App\Console\Commands;

use App\Models\Pesan;
use App\Services\PesanService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelPesananCommand extends Command
{
    // Nama perintah yang akan dipanggil di console.php
    protected $signature = 'pesanan:auto-cancel';
    protected $description = 'Membatalkan pesanan dari web yang belum dibayar setelah 1x24 jam';

    public function handle()
    {
        // Cari pesanan yang umurnya lebih dari 1 hari (24 jam yang lalu)
        $batasWaktu = Carbon::now()->subDay();

        $pesananKadaluarsa = Pesan::where('sumber_pesanan', 'bikincetak.co.id')
            ->where('status_pembayaran', 'belum_lunas')
            ->where('status_operasional', '!=', 'batal')
            ->where('tanggal_pesan', '<', $batasWaktu)
            ->get();

        $count = 0;
        foreach ($pesananKadaluarsa as $pesan) {
            $dataLama = PesanService::getSnapshotPesanan($pesan->id_pesan);

            // Ubah status jadi batal
            $pesan->status_operasional = 'batal';
            $pesan->save();

            $dataBaru = PesanService::getSnapshotPesanan($pesan->id_pesan);

            // Catat log agar admin tahu ini dibatalkan oleh sistem
            PesanService::catatLog(
                $pesan->id_pesan,
                'batal',
                'Sistem otomatis membatalkan pesanan karena melewati batas waktu pembayaran 1x24 jam.',
                $dataLama,
                $dataBaru
            );

            $count++;
        }

        $this->info("Berhasil membatalkan {$count} pesanan yang kedaluwarsa.");

        if ($count > 0) {
            Log::info("Auto-Cancel: {$count} pesanan dari web dibatalkan karena kedaluwarsa.");
        }
    }
}
