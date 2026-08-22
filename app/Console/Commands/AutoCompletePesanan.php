<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pesan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCompletePesanan extends Command
{
    // Nama untuk dipanggil di terminal / scheduler
    protected $signature = 'pesanan:auto-complete';
    protected $description = 'Menyelesaikan pesanan secara otomatis berdasarkan estimasi ekspedisi + 3 hari.';

    public function handle()
    {
        // Ambil semua pesanan yang statusnya sedang diantar
        $pesananDiJalan = Pesan::where('status_operasional', 'proses_pengantaran')->get();
        $countSelesai = 0;

        foreach ($pesananDiJalan as $pesanan) {
            try {
                // Mencegah error jika ekspedisi_estimasi kosong / null
                $estimasiString = $pesanan->ekspedisi_estimasi ?: '1';

                // Cari semua angka dalam string estimasi (misal: "2-3 Hari" -> [2, 3])
                preg_match_all('/\d+/', $estimasiString, $matches);

                $maxEstimasi = 1; // Default
                if (!empty($matches[0])) {
                    // Coba ambil angka terbesar (aman karena preg_match_all mengembalikan string)
                    $maxEstimasi = max(array_map('intval', $matches[0]));
                }

                // Kalkulasi deadline = tanggal update + estimasi kurir + 3 hari toleransi
                $totalHariTunggu = $maxEstimasi + 3;
                $deadline = Carbon::parse($pesanan->updated_at)->addDays($totalHariTunggu);

                // Eksekusi jika waktu sekarang sudah melewati deadline
                if (now()->greaterThanOrEqualTo($deadline)) {
                    $pesanan->status_operasional = 'selesai';
                    $pesanan->tanggal_selesai = now();
                    $pesanan->save();

                    Log::info("AUTO-COMPLETE: Pesanan {$pesanan->id_pesan} diselesaikan otomatis. Estimasi kurir: {$maxEstimasi} hari. Melewati deadline {$deadline->format('Y-m-d H:i')}.");

                    $countSelesai++;
                }
            } catch (\Exception $e) {
                // Tangkap error per pesanan agar looping pesanan selanjutnya tidak mati (Crash Protection)
                Log::error("GAGAL AUTO-COMPLETE untuk Pesanan {$pesanan->id_pesan}: " . $e->getMessage());
                $this->error("Gagal memproses pesanan {$pesanan->id_pesan}");
            }
        }

        $this->info("✅ Sukses: {$countSelesai} pesanan berhasil diselesaikan otomatis.");
    }
}
