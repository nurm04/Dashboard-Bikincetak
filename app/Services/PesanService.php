<?php

namespace App\Services;

use App\Mail\CheckoutSuccessMail;
use App\Models\Komposisi;
use App\Models\Pesan;
use App\Models\PesananLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PesanService
{
    public static function generateId(): string
    {
        $prefix = 'SO-' . date('ymd') . '-';

        $latest = Pesan::where('id_pesan','like',$prefix . '%')
            ->orderBy('id_pesan', 'desc')
            ->first();

        $number = $latest ? (int) substr($latest->id_pesan, -4) + 1 : 1;

        return $prefix . str_pad($number,4,'0',STR_PAD_LEFT);
    }

    public static function generateKodeTransaksi($length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLength = strlen($characters);

        do {
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charLength - 1)];
            }

            $exists = Pesan::where('kode_transaksi', $randomString)->exists();

        } while ($exists);

        return $randomString;
    }

    public static function generateKodeUnik(string $idPesan): int
    {
        preg_match('/(\d+)$/', $idPesan, $matches);
        $nomorUrut = (int) ($matches[1] ?? 1);
        return ($nomorUrut % 100) + 1;
    }

    public static function catatLog($id_pesan, $aksi, $keterangan = null, $dataLama = null, $dataBaru = null, $idStaf = null)
    {
        if (is_null($idStaf)) {
            $staf = auth()->user()?->staf;
            $idStaf = $staf ? $staf->id_staf : null;
        }

        return PesananLog::create([
            'id_pesan'   => $id_pesan,
            'id_staf'    => $idStaf,
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'data_lama'  => $dataLama,
            'data_baru'  => $dataBaru,
        ]);
    }

    public static function getSnapshotPesanan($id_pesan)
    {
        $pesanan = Pesan::with(['pesananItem.pesananItemFinishing'])->find($id_pesan);
        return $pesanan ? $pesanan->toArray() : null;
    }

    public static function kalkulasiRincianPesanan(Pesan $pesan): array
    {
        $totalProdukKotor = 0;
        $totalDiskonItem = 0;
        $totalSla = 0;

        foreach ($pesan->pesananItem as $item) {
            $qty = $item->jumlah;
            $hargaDasarAwal = $item->harga_dasar_awal_snapshot ?? $item->harga_satuan_snapshot;
            $diskonPerItem = $item->total_diskon_snapshot ?? 0;

            $atribut = is_string($item->atribut_custom_snapshot)
                ? json_decode($item->atribut_custom_snapshot, true)
                : ($item->atribut_custom_snapshot ?? []);

            $sisi = 1;
            foreach ($item->pesananItemFinishing as $fin) {
                $namaFinishing = strtolower($fin->nama_finishing_snapshot ?? '');
                if (str_contains($namaFinishing, 'dua sisi') || str_contains($namaFinishing, '2 sisi') || str_contains($namaFinishing, 'bolak')) {
                    $sisi = 2;
                    break;
                }
            }

            $biayaHalaman = 0;

            // ==========================================
            // LOGIC BUKU & LOGIC CETAK METERAN (SPANDUK)
            // ==========================================
            if (is_array($atribut) && isset($atribut['Jumlah Halaman'])) {
                $hal = max(1, (int) $atribut['Jumlah Halaman']);
                $biayaHalaman = max(0, $hal - 1) * $sisi * 1500;
            } elseif (is_array($atribut) && isset($atribut['Luas Dihargai (m2)'])) {
                $luasDihargai = (float) $atribut['Luas Dihargai (m2)'];
                // Minimal luas yang dihitung standar percetakan adalah 1 m2
                if ($luasDihargai < 1) $luasDihargai = 1;

                // Kalikan harga dasar dengan luas meteran
                $hargaDasarAwal = $hargaDasarAwal * $luasDihargai;
            }

            // Hitung harga bersih dipotong diskon (dilakukan setelah dikali Luas)
            $hargaSatuanNet = max(0, $hargaDasarAwal - $diskonPerItem);
            $hargaSatuProdukFull = $hargaSatuanNet + $biayaHalaman;

            $totalDiskonItem += ($diskonPerItem * $qty);

            $totalFinishingItem = 0;
            foreach ($item->pesananItemFinishing as $fin) {
                $biayaFin = 0;
                if ($fin->tipe === 'persen') {
                    $biayaFin = $hargaSatuProdukFull * ((float) $fin->harga_finishing_snapshot / 100);
                } else {
                    $biayaFin = (float) $fin->harga_finishing_snapshot;
                }

                $isKaliQty = (bool) optional($fin->skuFinishing)->kali_jumlah_pesan;

                if ($isKaliQty) {
                    $biayaFin *= $qty;
                }

                $totalFinishingItem += $biayaFin;
            }

            // Totalkan menggunakan base harga yang sudah dikali luas
            $kotorItem = (($hargaDasarAwal + $biayaHalaman) * $qty) + $totalFinishingItem;
            $totalProdukKotor += $kotorItem;

            $totalSla += $item->harga_pengerjaan_snapshot ?? 0;
        }

        $totalProdukBersih = $totalProdukKotor - $totalDiskonItem;
        $subtotal = $totalProdukBersih + $totalSla;

        $ongkir = (int) $pesan->harga_ongkir;
        $diskonVoucher = (int) $pesan->diskon_voucher_nominal;

        $kodeUnik = self::generateKodeUnik($pesan->id_pesan);

        $grandTotal = $subtotal + $ongkir + $kodeUnik - $diskonVoucher;
        $grandTotal = max(0, $grandTotal);

        $totalDibayar = $pesan->relationLoaded('pembayaran')
            ? $pesan->pembayaran->where('status_pembayaran', 'berhasil')->sum('nominal_bayar')
            : 0;

        $sisaTagihan = max(0, $grandTotal - $totalDibayar);

        return [
            'total_produk_kotor' => (int) $totalProdukKotor,
            'total_diskon_item'  => (int) $totalDiskonItem,
            'total_produk'       => (int) $totalProdukBersih,
            'total_sla'          => (int) $totalSla,
            'subtotal'           => (int) $subtotal,
            'ongkir'             => $ongkir,
            'diskon_voucher'     => $diskonVoucher,
            'kode_unik'          => $kodeUnik,
            'grand_total'        => (int) $grandTotal,
            'total_dibayar'      => (int) $totalDibayar,
            'sisa_tagihan'       => (int) $sisaTagihan,
        ];
    }

    public static function hitungTotalPesanan(Pesan $pesan): int
    {
        $rincian = self::kalkulasiRincianPesanan($pesan);
        return $rincian['grand_total'];
    }

    public static function hitungDeadlineKerja($items)
    {
        $maxJam = 0;
        $maxHari = 0;

        // 1. Looping array item untuk mencari SLA terlama
        foreach ($items as $item) {
            $estimasi = '';

            // DETEKSI SMART: Cek apakah item berupa Array (dari Request API)
            // atau Object Model (dari query Database PesananItem)
            if (is_array($item)) {
                $estimasi = $item['estimasi_pengerjaan'] ?? $item['estimasi_pengerjaan_snapshot'] ?? '';
            } elseif (is_object($item)) {
                $estimasi = $item->estimasi_pengerjaan ?? $item->estimasi_pengerjaan_snapshot ?? '';
            }

            // Regex untuk format Hari (contoh: "1 Hari", "2 Hari Reguler")
            if (preg_match('/(\d+)\s*hari/i', $estimasi, $matches)) {
                $hari = (int) $matches[1];
                if ($hari > $maxHari) $maxHari = $hari;
            }
            // Regex untuk format Jam (contoh: "2 Jam", "12 Jam Kilat")
            elseif (preg_match('/(\d+)\s*jam/i', $estimasi, $matches)) {
                $jam = (int) $matches[1];
                if ($jam > $maxJam) $maxJam = $jam;
            }
        }

        // Fallback default jika teks tidak jelas / murni kosong (Otomatis 1 Hari)
        if ($maxHari === 0 && $maxJam === 0) {
            $maxHari = 1;
        }

        $waktu = Carbon::now();

        // ============================================
        // PRIORITAS 1: KALKULASI JIKA ADA FORMAT "HARI" TERLAMA
        // ============================================
        if ($maxHari > 0) {
            for ($i = 0; $i < $maxHari; $i++) {
                $waktu->addDay();
                if ($waktu->isSunday()) {
                    $waktu->addDay(); // Lompati hari Minggu
                }
            }
            // Sesuaikan dengan jam operasional toko
            if ($waktu->hour >= 17) {
                $waktu->addDay()->setTime(7, 0, 0);
                if ($waktu->isSunday()) $waktu->addDay();
            } elseif ($waktu->hour < 7) {
                $waktu->setTime(7, 0, 0);
            }
            return $waktu;
        }

        // ============================================
        // PRIORITAS 2: KALKULASI JIKA MURNI FORMAT "JAM" (Cth: 2 Jam Kilat)
        // Hanya memakan waktu pada Jam Operasional (07:00 - 17:00)
        // ============================================
        if ($maxJam > 0) {
            // Jika order masuk di luar jam kerja, geser waktu mulai ke jam buka terdekat
            if ($waktu->isSunday()) {
                $waktu->addDay()->setTime(7, 0, 0);
            } elseif ($waktu->hour >= 17) {
                $waktu->addDay()->setTime(7, 0, 0);
                if ($waktu->isSunday()) $waktu->addDay();
            } elseif ($waktu->hour < 7) {
                $waktu->setTime(7, 0, 0);
            }

            $sisaMenit = $maxJam * 60; // Ubah ke menit agar perhitungannya presisi

            while ($sisaMenit > 0) {
                $tutup = $waktu->copy()->setTime(17, 0, 0);
                $menitTersisaHariIni = $waktu->diffInMinutes($tutup);

                if ($sisaMenit <= $menitTersisaHariIni) {
                    // Sisa pengerjaan bisa diselesaikan hari ini juga
                    $waktu->addMinutes($sisaMenit);
                    $sisaMenit = 0;
                } else {
                    // Waktu hari ini keburu habis (toko tutup), sisa pekerjaan dilanjut besok paginya
                    $sisaMenit -= $menitTersisaHariIni;
                    $waktu->addDay()->setTime(7, 0, 0);

                    // Pastikan besok bukan hari libur (Minggu)
                    if ($waktu->isSunday()) {
                        $waktu->addDay();
                    }
                }
            }
            return $waktu;
        }

        return $waktu;
    }

    public static function hitungTotalBeratPesanan(Pesan $pesan): int
    {
        return (int) $pesan->pesananItem->sum('total_berat_snapshot');
    }

    public static function hitungBeratTotalItem(string $idSku, int $jumlah, array $selectedFinishingIds = [], array $atributCustom = []): int
    {
        $komposisiList = Komposisi::with('bahanBaku')
                            ->where('id_sku', $idSku)
                            ->where(function ($query) use ($selectedFinishingIds) {
                                $query->whereNull('id_pilihan_finishing')
                                    ->orWhereIn('id_pilihan_finishing', $selectedFinishingIds);
                            })
                            ->get();

        $pengaliBahanUtama = 1;

        if (isset($atributCustom['Jumlah Halaman'])) {
            $pengaliBahanUtama = max(1, (int)$atributCustom['Jumlah Halaman']);
        } elseif (isset($atributCustom['Luas Dihargai (m2)'])) {
            $pengaliBahanUtama = (float) str_replace(',', '.', $atributCustom['Luas Dihargai (m2)']);
        } elseif (isset($atributCustom['Panjang']) && isset($atributCustom['Lebar'])) {
            $panjang = (float) str_replace(',', '.', $atributCustom['Panjang']);
            $lebar = (float) str_replace(',', '.', $atributCustom['Lebar']);
            $pengaliBahanUtama = $panjang * $lebar;
        }

        $beratSatuPcs = 0;

        foreach ($komposisiList as $komp) {
            if ($komp->bahanBaku) {
                $beratBahan = $komp->jumlah_pakai * $komp->bahanBaku->berat_gram_persatuan;

                if (is_null($komp->id_pilihan_finishing)) {
                    $beratBahan *= $pengaliBahanUtama;
                }

                $beratSatuPcs += $beratBahan;
            }
        }

        return (int) ceil($beratSatuPcs * $jumlah);
    }

    public static function buildStatusMessage(Pesan $pesan, string $status): string
    {
        $nama = $pesan->customer->user->name;

        $teksEkspedisi = $pesan->ekspedisi_nama
            ? " menggunakan layanan pengiriman {$pesan->ekspedisi_nama} ({$pesan->ekspedisi_layanan})"
            : "";

        return match ($status) {

        'proses_pengerjaan' =>
"🔨 PESANAN DIPROSES

Halo {$nama},

Pesanan Anda dengan Kode Transaksi:

{$pesan->kode_transaksi}

Saat ini sedang masuk tahap pengerjaan.

Kami akan memberi kabar kembali ketika pesanan siap dikirim.

Terima kasih.",

        'proses_pengantaran' =>
"🚚 PESANAN DIKIRIM

Halo {$nama},

Pesanan Anda dengan Kode Transaksi:

{$pesan->kode_transaksi}

Saat ini sedang dalam proses pengantaran{$teksEkspedisi}.

Silakan menunggu hingga pesanan tiba di alamat tujuan.

Terima kasih.",

        'selesai' =>
"✅ PESANAN SELESAI

Halo {$nama},

Pesanan dengan Kode Transaksi:

{$pesan->kode_transaksi}

Telah selesai.

Terima kasih telah menggunakan layanan Bikin Cetak.

Kami tunggu pesanan berikutnya 🙏",

        'batal' =>
"❌ PESANAN DIBATALKAN

Halo {$nama},

Pesanan dengan Kode Transaksi:

{$pesan->kode_transaksi}

Telah dibatalkan.

Jika terdapat pertanyaan silakan hubungi admin.

Terima kasih.",

            default => ''
        };
    }

    public static function kirimNotifikasiStatus(Pesan $pesan, string $status): void
    {
        try {
            $message = self::buildStatusMessage($pesan, $status);

            if (!$message) return;

            self::sendWhatsapp($pesan->customer->no_hp, $message);

            Mail::raw(
                $message,
                function ($mail) use ($pesan, $status) {

                    $judul = match ($status) {
                        'proses_pengerjaan' => 'Pesanan Sedang Diproses',
                        'proses_pengantaran' => 'Pesanan Sedang Dikirim',
                        'selesai' => 'Pesanan Telah Selesai',
                        'batal' => 'Pesanan Dibatalkan',
                        default => 'Update Status Pesanan'
                    };

                    $mail->to($pesan->customer->user->email)
                        ->subject($judul . ' - ' . $pesan->id_pesan);
                }
            );
        } catch (\Throwable $e) {
            Log::error(
                'Gagal kirim notifikasi status',
                [
                    'id_pesan' => $pesan->id_pesan,
                    'status' => $status,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    public static function sendWhatsapp(string $nomor, string $pesan, ?string $file = null): bool
    {
        try {
            $nomor = preg_replace('/[^0-9]/', '', $nomor);

            if (str_starts_with($nomor, '0')) {
                $nomor = '62' . substr($nomor, 1);
            }

            $payload = [
                'target' => $nomor,
                'message' => $pesan,
            ];

            if ($file) $payload['file'] = $file;

            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post(
                'https://api.fonnte.com/send',
                $payload
            );

            Log::info('Fonnte Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $response->successful();

        } catch (\Throwable $e) {
            Log::error('WhatsApp Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function buildCheckoutMessage(Pesan $pesan, int $totalPesanan, int $kodeUnik, array $rekening): string
    {
        $totalTransfer = $totalPesanan + $kodeUnik;

        return
"🛒 PESANAN BERHASIL DIBUAT

Kode Transaksi:
{$pesan->kode_transaksi}

Total Pesanan:
Rp " . number_format($totalPesanan,0,',','.') . "

Kode Unik:
{$kodeUnik}

Total Transfer:
Rp " . number_format($totalTransfer,0,',','.') . "

Transfer ke:

Bank {$rekening['bank']}
No. Rek {$rekening['nomor']}
a.n {$rekening['atas_nama']}

Mohon transfer sesuai nominal agar pembayaran dapat diverifikasi.

Terima kasih.";
    }

    public static function kirimNotifikasiCheckout(Pesan $pesan, int $totalPesanan, int $kodeUnik, array $rekening): void
    {

        try {

            $totalTransfer = $totalPesanan + $kodeUnik;

            try {

                Log::info(
                    'Mulai kirim email checkout',
                    [
                        'email' => $pesan->customer->user->email,
                        'id_pesan' => $pesan->id_pesan,
                    ]
                );

                Mail::to(
                    $pesan->customer->user->email
                )->send(
                    new CheckoutSuccessMail(
                        $pesan,
                        $totalTransfer,
                        $kodeUnik,
                        $rekening
                    )
                );

                Log::info(
                    'Email checkout berhasil terkirim',
                    [
                        'email' => $pesan->customer->user->email,
                        'id_pesan' => $pesan->id_pesan,
                    ]
                );

            } catch (\Throwable $e) {

                Log::error(
                    'Gagal kirim email checkout',
                    [
                        'id_pesan' => $pesan->id_pesan,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]
                );
            }

            $message =
                self::buildCheckoutMessage(
                    $pesan,
                    $totalPesanan,
                    $kodeUnik,
                    $rekening
                );

            self::sendWhatsapp(
                $pesan->customer->no_hp,
                $message
            );

        } catch (\Throwable $e) {

            Log::error(
                'Gagal kirim notifikasi checkout',
                [
                    'id_pesan' => $pesan->id_pesan,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }
}
