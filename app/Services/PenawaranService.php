<?php

namespace App\Services;

use App\Models\Penawaran;
use App\Mail\PenawaranMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PenawaranService
{
    /**
     * Kirim Email Admin dengan Attachment File PDF
     */
    public static function kirimEmailPenawaran(Penawaran $penawaran): bool
    {
        try {
            $emailTujuan = $penawaran->customer->user->email ?? null;
            if (!$emailTujuan) {
                Log::error('Gagal kirim email: Customer tidak punya email');
                return false;
            }

            // Load View Blade PDF
            $pdf = Pdf::loadView('pdf.penawaran', ['penawaran' => $penawaran])
                      ->setPaper('a4', 'portrait');

            $pdfContent = $pdf->output();

            Mail::to($emailTujuan)->send(new PenawaranMail($penawaran, $pdfContent));

            return true;
        } catch (\Throwable $e) {
            Log::error('Gagal kirim email penawaran', [
                'id_penawaran' => $penawaran->id_penawaran,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Kirim WA Penawaran (Pesan Teks Saja - Tanpa Lampiran File)
     */
    public static function kirimWaPenawaran(Penawaran $penawaran): bool
    {
        try {
            $noHp = $penawaran->customer->no_hp ?? null;
            if (!$noHp) return false;

            $noHp = preg_replace('/[^0-9]/', '', $noHp);
            if (str_starts_with($noHp, '0')) {
                $noHp = '62' . substr($noHp, 1);
            }

            $nama = $penawaran->customer->user->name ?? 'Bapak/Ibu';
            $tglValid = \Carbon\Carbon::parse($penawaran->berlaku_sampai)->translatedFormat('d F Y');

            $pesan = "📄 *SURAT PENAWARAN (QUOTATION)*\n\n";
            $pesan .= "Halo {$nama},\n\n";
            $pesan .= "Terima kasih atas ketertarikan Anda pada layanan Bikin Cetak. Berikut rincian Surat Penawaran resmi Anda:\n\n";
            $pesan .= "▪ *No. Penawaran:* {$penawaran->id_penawaran}\n";
            $pesan .= "▪ *Berlaku Sampai:* {$tglValid}\n\n";
            $pesan .= "Dokumen resmi penawaran harga telah kami kirimkan ke alamat Email Anda.\n\n";
            $pesan .= "Jika ada pertanyaan lebih lanjut, Anda dapat membalas pesan ini.\n\nTerima Kasih 🙏";

            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post('https://api.fonnte.com/send', [
                'target' => $noHp,
                'message' => $pesan,
            ]);

            return $response->successful();

        } catch (\Throwable $e) {
            Log::error('Gagal kirim WA penawaran: ' . $e->getMessage());
            return false;
        }
    }
}
