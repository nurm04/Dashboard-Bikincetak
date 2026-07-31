<?php

namespace App\Services;

use App\Mail\CheckoutSuccessMail;
use App\Models\Komposisi;
use App\Models\Pesan;
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

    public static function kalkulasiRincianPesanan(Pesan $pesan): array
    {
        $totalProdukKotor = 0;
        $totalDiskonItem = 0;
        $totalSla = 0;

        foreach ($pesan->pesananItem as $item) {
            $qty = $item->jumlah;
            $hargaDasarAwal = $item->harga_dasar_awal_snapshot ?? $item->harga_satuan_snapshot;
            $diskonPerItem = $item->total_diskon_snapshot ?? 0;

            $hargaSatuanNet = max(0, $hargaDasarAwal - $diskonPerItem);

            $atribut = is_string($item->atribut_custom_snapshot)
                ? json_decode($item->atribut_custom_snapshot, true)
                : $item->atribut_custom_snapshot;

            $sisi = 1;
            foreach ($item->pesananItemFinishing as $fin) {
                $namaFinishing = strtolower($fin->nama_finishing_snapshot ?? '');
                if (str_contains($namaFinishing, 'dua sisi') || str_contains($namaFinishing, '2 sisi') || str_contains($namaFinishing, 'bolak')) {
                    $sisi = 2;
                    break;
                }
            }

            $biayaHalaman = 0;
            if (is_array($atribut) && isset($atribut['Jumlah Halaman'])) {
                $hal = max(1, (int) $atribut['Jumlah Halaman']);
                $biayaHalaman = max(0, $hal - 1) * $sisi * 1500;
            }

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

                if ($isKaliQty) {
                    $biayaFin *= $qty;
                }

                $totalFinishingItem += $biayaFin;
            }

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

        // CUMA AMBIL JUMLAH HALAMAN (Tanpa konversi sisi cetak)
        $jumlahHalaman = isset($atributCustom['Jumlah Halaman']) ? max(1, (int)$atributCustom['Jumlah Halaman']) : 1;

        $beratSatuPcs = 0;

        foreach ($komposisiList as $komp) {
            if ($komp->bahanBaku) {
                // RUMUS LU: Berat Bahan (32.76) x Pemakaian BOM (0.25)
                $beratBahan = $komp->jumlah_pakai * $komp->bahanBaku->berat_gram_persatuan;

                // JIKA INI BAHAN UTAMA (Kertas Isi Buku), KALIKAN DENGAN HALAMAN (20)
                if (is_null($komp->id_pilihan_finishing)) {
                    $beratBahan *= $jumlahHalaman;
                }

                // (Untuk finishing seperti cover dll, dia akan masuk ke blok $beratSatuPcs tanpa dikali halaman)

                $beratSatuPcs += $beratBahan;
            }
        }

        // Total akhir dikalikan dengan QTY pesanan
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
