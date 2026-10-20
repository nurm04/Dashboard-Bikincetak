<?php

namespace App\Services;

use App\Mail\PenawaranMail;
use App\Models\Penawaran;
use App\Services\PesanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
     * Kirim WA Penawaran (Dengan Lampiran File PDF Langsung)
     */
    public static function kirimWaPenawaran(Penawaran $penawaran): bool
    {
        try {
            $noHp = $penawaran->customer->no_hp ?? null;
            if (!$noHp) {
                Log::warning('BalesOtomatis Warning: Nomor HP customer kosong untuk penawaran ID: ' . $penawaran->id_penawaran);
                return false;
            }

            $nama = $penawaran->customer->user->name ?? 'Bapak/Ibu';
            $tglValid = \Carbon\Carbon::parse($penawaran->berlaku_sampai)->translatedFormat('d F Y');

            // 1. Generate PDF
            $pdf = Pdf::loadView('pdf.penawaran', ['penawaran' => $penawaran])
                    ->setPaper('a4', 'portrait');

            $fileName = 'Penawaran_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $penawaran->id_penawaran) . '.pdf';
            $relativePath = 'pdf/penawaran/' . $fileName;

            // Pastikan folder penyimpanan ada (mencegah error missing directory)
            if (!Storage::disk('public')->exists('pdf/penawaran')) {
                Storage::disk('public')->makeDirectory('pdf/penawaran');
            }

            // Simpan PDF ke disk public
            Storage::disk('public')->put($relativePath, $pdf->output());

            // Dapatkan URL Publik PDF
            $pdfUrl = asset('storage/' . $relativePath);

            // 2. Susun Pesan Teks Pengantar & Kirim via BalesOtomatis
            $pesan = "📄 *SURAT PENAWARAN (QUOTATION)*\n\n";
            $pesan .= "Halo {$nama},\n\n";
            $pesan .= "Terima kasih atas ketertarikan Anda pada layanan Bikin Cetak. Berikut rincian Surat Penawaran resmi Anda:\n\n";
            $pesan .= "▪ *No. Penawaran:* {$penawaran->id_penawaran}\n";
            $pesan .= "▪ *Berlaku Sampai:* {$tglValid}\n\n";
            $pesan .= "Dokumen PDF surat penawaran resmi telah kami lampirkan bersama pesan ini.\n\n";
            $pesan .= "Jika ada pertanyaan lebih lanjut, Anda dapat membalas pesan ini.\n\nTerima Kasih 🙏";

            return PesanService::sendWhatsapp($noHp, $pesan, $pdfUrl);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim WA penawaran: ' . $e->getMessage());
            return false;
        }
    }
}
