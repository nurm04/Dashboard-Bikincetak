<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Staf;
use App\Models\Absensi;
use Carbon\Carbon;

class CheckAlphaAbsensi extends Command
{
    // Nama untuk dipanggil di terminal / scheduler
    protected $signature = 'absen:check-alpha';
    protected $description = 'Mengecek dan mencatat staf yang tidak absen (Alpha) atau lupa pulang pada hari kerja.';

    public function handle()
    {
        $hariIni = Carbon::now();

        // 1. Skip kalau hari Minggu
        if ($hariIni->isSunday()) {
            $this->info('Hari Minggu libur. Proses pengecekan absensi dibatalkan.');
            return;
        }

        $tanggal = $hariIni->toDateString();
        $stafs = Staf::all();

        $countAlpha = 0;
        $countLupaPulang = 0;

        foreach ($stafs as $staf) {
            $absen = Absensi::where('id_staf', $staf->id_staf)
                            ->where('tanggal', $tanggal)
                            ->first();

            if (!$absen) {
                // Insert Alpha
                Absensi::create([
                    'id_staf' => $staf->id_staf,
                    'tanggal' => $tanggal,
                    'status'  => 'Alpha',
                    'keterangan' => 'Tidak melakukan absensi (Dicatat otomatis oleh sistem)',
                ]);
                $countAlpha++;
            } else {
                // Logic Lupa Pulang (Absen masuk ada, tapi jam_keluar kosong)
                if ($absen->jam_masuk && !$absen->jam_keluar && !in_array($absen->status, ['Izin', 'Sakit', 'Alpha'])) {
                    $keteranganLama = $absen->keterangan ? $absen->keterangan . ' | ' : '';
                    $absen->keterangan = $keteranganLama . 'Lupa absen pulang (Dicatat otomatis oleh sistem)';
                    $absen->save();

                    $countLupaPulang++;
                }
            }
        }

        $this->info("✅ Sukses: {$countAlpha} staf Alpha, {$countLupaPulang} staf lupa absen pulang dicatat.");
    }
}
