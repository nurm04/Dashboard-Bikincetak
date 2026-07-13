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
        $prefix = 'PSN-' . date('ymd') . '-';

        $latest = Pesan::where('id_pesan','like',$prefix . '%')
            ->orderBy('id_pesan', 'desc')
            ->first();

        $number = $latest ? (int) substr($latest->id_pesan, -4) + 1 : 1;

        return $prefix . str_pad($number,4,'0',STR_PAD_LEFT);
    }

    public static function generateKodeUnik(string $idPesan): int
    {
        preg_match('/(\d+)$/', $idPesan, $matches);
        $nomorUrut = (int) ($matches[1] ?? 1);
        return ($nomorUrut % 100) + 1;
    }

    public static function kalkulasiRincianPesanan(Pesan $pesan): array
    {
        $totalProduk = 0;
        $totalSla = 0;

        foreach ($pesan->pesananItem as $item) {
            $totalFinishing = $item->pesananItemFinishing->sum('harga_finishing_snapshot');

            $totalProduk += ($item->harga_satuan_snapshot + $totalFinishing) * $item->jumlah;

            $totalSla += $item->harga_pengerjaan_snapshot;
        }

        $subtotal = $totalProduk + $totalSla;

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
            'total_produk'   => (int) $totalProduk,
            'total_sla'      => (int) $totalSla,
            'subtotal'       => (int) $subtotal,
            'ongkir'         => $ongkir,
            'diskon_voucher' => $diskonVoucher,
            'kode_unik'      => $kodeUnik,
            'grand_total'    => (int) $grandTotal,
            'total_dibayar'  => (int) $totalDibayar,
            'sisa_tagihan'   => (int) $sisaTagihan,
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

    public static function hitungBeratTotalItem(string $idSku, int $jumlah, array $selectedFinishingIds = []): int
    {
        $komposisiList = Komposisi::with('bahanBaku')
                            ->where('id_sku', $idSku)
                            ->where(function ($query) use ($selectedFinishingIds) {
                                $query->whereNull('id_pilihan_finishing')
                                      ->orWhereIn('id_pilihan_finishing', $selectedFinishingIds);
                            })
                            ->get();

        $beratSatuPcs = 0;

        foreach ($komposisiList as $komp) {
            if ($komp->bahanBaku) {
                $beratBahan = $komp->jumlah_pakai * $komp->bahanBaku->berat_gram_persatuan;
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

Pesanan Anda dengan ID:

{$pesan->id_pesan}

Saat ini sedang masuk tahap pengerjaan.

Kami akan memberi kabar kembali ketika pesanan siap dikirim.

Terima kasih.",

        'proses_pengantaran' =>
"🚚 PESANAN DIKIRIM

Halo {$nama},

Pesanan Anda dengan ID:

{$pesan->id_pesan}

Saat ini sedang dalam proses pengantaran{$teksEkspedisi}.

Silakan menunggu hingga pesanan tiba di alamat tujuan.

Terima kasih.",

        'selesai' =>
"✅ PESANAN SELESAI

Halo {$nama},

Pesanan dengan ID:

{$pesan->id_pesan}

Telah selesai.

Terima kasih telah menggunakan layanan Bikin Cetak.

Kami tunggu pesanan berikutnya 🙏",

        'batal' =>
"❌ PESANAN DIBATALKAN

Halo {$nama},

Pesanan dengan ID:

{$pesan->id_pesan}

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

            /*
            |--------------------------------------------------------------------------
            | WHATSAPP
            |--------------------------------------------------------------------------
            */

            self::sendWhatsapp($pesan->customer->no_hp, $message);

            /*
            |--------------------------------------------------------------------------
            | EMAIL
            |--------------------------------------------------------------------------
            */

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

ID Pesanan:
{$pesan->id_pesan}

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

            /*
            |--------------------------------------------------------------------------
            | EMAIL
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | WHATSAPP
            |--------------------------------------------------------------------------
            */

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
