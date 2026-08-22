<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 👇 DAFTAR JADWAL OTOMATIS (CRON JOBS) 👇

// 1. Auto-Selesaikan Pesanan setiap jam 01:00 dini hari
Schedule::command('pesanan:auto-complete')->dailyAt('01:00');

// 2. Cek Staf Alpha setiap jam 23:50 malam (Senin-Sabtu)
// Metode ->days([1,2,3,4,5,6]) memastikan tidak jalan di hari Minggu (0)
Schedule::command('absen:check-alpha')->dailyAt('23:50')->days([1, 2, 3, 4, 5, 6]);
