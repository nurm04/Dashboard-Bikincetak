<?php

namespace App\Mail;

use App\Models\PengaturanWeb;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pesan;
    public $totalTransfer;
    public $kodeUnik;
    public $rekening;
    public array $rekeningList;

    /**
     * Create a new message instance.
     */
    public function __construct($pesan, $totalTransfer, $kodeUnik, $rekening = [])
    {
        $this->pesan = $pesan;
        $this->totalTransfer = $totalTransfer;
        $this->kodeUnik = $kodeUnik;
        $this->rekening = $rekening;
        $this->rekeningList = $this->resolveRekeningFromPengaturan($rekening);
    }

    private function resolveRekeningFromPengaturan($fallbackRekening): array
    {
        $pengaturanPembayaran = PengaturanWeb::where('kunci', 'metode_pembayaran')->first();

        if ($pengaturanPembayaran && !empty($pengaturanPembayaran->nilai)) {
            $decoded = is_string($pengaturanPembayaran->nilai)
                ? json_decode($pengaturanPembayaran->nilai, true)
                : $pengaturanPembayaran->nilai;

            if (is_array($decoded)) {
                $filtered = array_values(array_filter($decoded, function ($item) {
                    $isActive = $item['is_active'] ?? true;
                    $noRek = trim((string) ($item['no_rekening'] ?? ''));
                    $namaMetode = strtolower(trim((string) ($item['nama_metode'] ?? '')));

                    return (bool) $isActive
                        && $noRek !== ''
                        && $noRek !== '-'
                        && !str_contains($namaMetode, 'qris');
                }));

                if (!empty($filtered)) {
                    return array_map(function ($rek) {
                        return [
                            'bank' => $rek['nama_metode'] ?? '-',
                            'nomor' => $rek['no_rekening'] ?? '-',
                            'atas_nama' => $rek['atas_nama'] ?? '-',
                        ];
                    }, $filtered);
                }
            }
        }

        if (is_array($fallbackRekening) && !empty($fallbackRekening)) {
            if (array_is_list($fallbackRekening)) {
                return $fallbackRekening;
            }
            return [$fallbackRekening];
        }

        return [];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Berhasil Dibuat - ' . $this->pesan->kode_transaksi,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout-success',
        );
    }
}
