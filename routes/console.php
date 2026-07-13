<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


use App\Models\Pesan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
Schedule::call(function () {

    $pesananDiJalan = Pesan::where('status_operasional', 'proses_pengantaran')->get();

    foreach ($pesananDiJalan as $pesanan) {

        $estimasiString = $pesanan->ekspedisi_estimasi ?? '1';
        preg_match_all('/\d+/', $estimasiString, $matches);

        $maxEstimasi = 1;
        if (!empty($matches[0])) {
            $maxEstimasi = max($matches[0]);
        }

        $totalHariTunggu = (int) $maxEstimasi + 3;

        $deadline = Carbon::parse($pesanan->updated_at)->addDays($totalHariTunggu);

        if (now()->greaterThanOrEqualTo($deadline)) {

            $pesanan->status_operasional = 'selesai';
            $pesanan->tanggal_selesai = now();
            $pesanan->save();

            Log::info("AUTO-COMPLETE: Pesanan {$pesanan->id_pesan} diselesaikan otomatis. Estimasi kurir: {$maxEstimasi} hari. Melewati deadline {$deadline->format('Y-m-d H:i')}.");
        }
    }

})->dailyAt('01:00');
