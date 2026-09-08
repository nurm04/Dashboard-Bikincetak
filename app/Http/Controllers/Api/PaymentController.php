<?php

namespace App\Http\Controllers\Api;

use App\Events\ProduksiBaruEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\BukuBesarController;
use App\Models\Pembayaran;
use App\Models\Pesan;
use App\Services\PembayaranService;
use App\Services\PesanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function uploadQrisKeKomerce()
    {
        $isProduction = env('APP_ENV') === 'production';
        $baseUrl = $isProduction ? env('KOMERCE_V2_LIVE_URL') : env('KOMERCE_V2_SANDBOX_URL');
        $baseUrl = rtrim($baseUrl, '/');

        $endpoint = $baseUrl . '/user/api/v1/qrisly/upload-qris';
        $pathGambar = storage_path('app/public/qris_asli.jpeg');

        if (!file_exists($pathGambar)) {
            return response()->json(['error' => 'File gambar tidak ditemukan di ' . $pathGambar]);
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key' => env('QRISLY_API_KEY'),
            ])->attach(
                'qris_image', file_get_contents($pathGambar), 'qris_asli.jpg'
            )->post($endpoint, [
                'name' => 'BikinCetak Official QRIS',
            ]);

            return response()->json([
                'status' => $response->status(),
                'komerce_response' => $response->json()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function generateQris(Request $request, $id_pesan)
    {
        $pesan = Pesan::with('pembayaran')->where('id_pesan', $id_pesan)->first();

        if (!$pesan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesan->status_pembayaran === 'lunas') {
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah lunas.'], 400);
        }

        $rincian = PesanService::kalkulasiRincianPesanan($pesan);
        $sisaTagihan = (int) ceil($rincian['sisa_tagihan']);

        $nominalBayar = $sisaTagihan;

        if ($request->has('nominal') && is_numeric($request->nominal)) {
            $requestNominal = (int) $request->nominal;
            $nominalBayar = ($requestNominal > $sisaTagihan) ? $sisaTagihan : $requestNominal;

            if ($nominalBayar < 1000) {
                return response()->json(['success' => false, 'message' => 'Minimal pembayaran QRIS adalah Rp 1.000'], 400);
            }
        }

        $isProduction = env('APP_ENV') === 'production';
        $baseUrl = $isProduction ? env('KOMERCE_V2_LIVE_URL') : env('KOMERCE_V2_SANDBOX_URL');
        $baseUrl = rtrim($baseUrl, '/');

        $endpoint = $baseUrl . '/user/api/v1/qrisly/generate-qris';

        try {
            $response = Http::withHeaders([
                'X-API-Key'    => env('QRISLY_API_KEY'),
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($endpoint, [
                'qris_id'       => (int) env('KOMERCE_QRIS_ID'),
                'amount'        => $nominalBayar,
                'output_type'   => 'string',
                'unique_amount' => true,
            ]);

            // JIKA SUKSES
            if ($response->successful()) {
                $data = $response->json();

                $qrString = $data['data']['qris_string'] ?? $data['data']['qr_string'] ?? null;
                $qrUrl = $data['data']['qr_url'] ?? null;
                $finalAmount = $data['data']['final_amount'] ?? $nominalBayar;
                $historyId = $data['data']['history_id'] ?? null;

                if (!$qrString && !$qrUrl) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format balasan Komerce tidak sesuai.'
                    ], 500);
                }

                // CATAT INVOICE PENDING KE TABEL PEMBAYARAN
                if ($historyId) {
                    $existingPayment = Pembayaran::where('reference_id', $historyId)
                                                 ->where('status_pembayaran', 'menunggu_pembayaran')
                                                 ->first();

                    if (!$existingPayment) {
                        Pembayaran::create([
                            'id_pembayaran'       => PembayaranService::generateId(),
                            'id_pesan'            => $pesan->id_pesan,
                            'nominal_bayar'       => $finalAmount,
                            'metode_pembayaran'   => 'qris',
                            'status_pembayaran'   => 'menunggu_pembayaran',
                            'payment_type_detail' => 'qris_komerce',
                            'reference_id'        => (string) $historyId,
                            'catatan'             => 'Menunggu pembayaran via QRIS Komerce'
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'QRIS berhasil di-generate',
                    'data' => [
                        'order_id'   => $pesan->id_pesan,
                        'history_id' => $historyId,
                        'amount'     => $finalAmount,
                        'qr_string'  => $qrString,
                        'qr_url'     => $qrUrl,
                    ]
                ]);
            }

            // 👇 JIKA DITOLAK KOMERCE (Bongkar Paksa Errornya) 👇
            $errorBody = $response->json();

            if (is_array($errorBody)) {
                $pesanErrorKomerce = $errorBody['meta']['message'] ?? $errorBody['message'] ?? json_encode($errorBody);
            } else {
                // Biasanya karena Komerce down (502 Gateway) dan mengembalikan format HTML
                $pesanErrorKomerce = 'Komerce Error HTML: ' . substr($response->body(), 0, 100);
            }

            Log::error("Komerce DITOLAK (Status {$response->status()}): " . $response->body());

            return response()->json([
                'success' => false,
                'message' => 'KOMERCE: ' . $pesanErrorKomerce
            ], 500);

        } catch (\Exception $e) {
            // 👇 JIKA SERVER LU YANG CRASH 👇
            Log::error('Exception QRIS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'CRASH BACKEND: ' . $e->getMessage() . ' (Baris ' . $e->getLine() . ')'
            ], 500);
        }
    }

    public function webhookKomerce(Request $request)
    {
        $payload = $request->all();
        Log::info('Komerce Webhook Masuk:', $payload);

        // 1. CEK FORMAT PAYLOAD (Sandbox Postman vs Production Komerce)
        $isProductionFormat = isset($payload['history_id']) && isset($payload['payment_status']);
        $isSandboxFormat = isset($payload['order_id']) && isset($payload['status']);

        if (!$isProductionFormat && !$isSandboxFormat) {
            Log::warning('Webhook Komerce Gagal: Format payload tidak dikenali.', $payload);
            return response()->json(['success' => false, 'message' => 'Format payload tidak valid'], 400);
        }

        // Tentukan variabel berdasarkan format yang masuk
        $statusBayar = $isProductionFormat ? $payload['payment_status'] : $payload['status'];

        // PENTING: Jika Production, kita pakai history_id. Jika Sandbox (Postman), pakai order_id.
        $identifier  = $isProductionFormat ? (string) $payload['history_id'] : $payload['order_id'];

        if ($statusBayar === 'SUCCESS' || $statusBayar === 'PAID') {

            // 👇 CARI DI TABEL PEMBAYARAN (JIKA PRODUCTION) ATAU TABEL PESAN (JIKA POSTMAN) 👇
            $pembayaranPending = null;
            $pesan = null;

            if ($isProductionFormat) {
                // Cari di tabel pembayaran berdasarkan reference_id (yang isinya history_id dari Komerce)
                $pembayaranPending = Pembayaran::where('reference_id', $identifier)
                                               ->where('status_pembayaran', 'menunggu_pembayaran')
                                               ->first();

                if ($pembayaranPending) {
                    // Kalau ketemu pembayaran pending-nya, ambil data pesanannya juga
                    $pesan = Pesan::with(['pesananItem.pesananItemFinishing', 'pembayaran'])->find($pembayaranPending->id_pesan);
                } else {
                     Log::warning("Webhook Komerce: Pembayaran pending dengan reference_id (history_id) {$identifier} tidak ditemukan.");
                     return response()->json(['success' => false, 'message' => 'Pembayaran pending tidak ditemukan.'], 404);
                }
            } else {
                // Sandbox postman (menggunakan kode transaksi / id pesan langsung)
                $pesan = Pesan::with(['pesananItem.pesananItemFinishing', 'pembayaran'])
                              ->where('id_pesan', $identifier)
                              ->orWhere('kode_transaksi', $identifier)
                              ->first();
            }

            if ($pesan) {
                if ($pesan->status_pembayaran !== 'lunas') {

                    $dataLama = PesanService::getSnapshotPesanan($pesan->id_pesan);

                    $rincian = PesanService::kalkulasiRincianPesanan($pesan);

                    $tagihanTanpaKodeUnik = $rincian['subtotal'] + $rincian['ongkir'] - $rincian['diskon_voucher'];
                    $totalTagihan = (int) ceil($rincian['grand_total']);

                    $nominalDibayar = $payload['amount'] ?? $payload['final_amount'] ?? $totalTagihan;
                    $nominalDibayar = (int) $nominalDibayar;

                    // 👇 JIKA TRANSAKSI DARI KOMERCE, UPDATE RECORD YANG PENDING 👇
                    if ($pembayaranPending) {
                        $pembayaranPending->status_pembayaran = 'berhasil';
                        $pembayaranPending->nominal_bayar = $nominalDibayar;
                        $pembayaranPending->catatan = 'Berhasil dibayar via QRIS Komerce';
                        $pembayaranPending->save();

                        // Catat jurnal buku besarnya
                        $akunKas = BukuBesarController::getAkunId('Kas Bank (BCA/Mandiri/dll)');
                        $akunLawan = in_array($pesan->status_operasional, ['proses_pengantaran', 'selesai'])
                                     ? BukuBesarController::getAkunId('Piutang Usaha (Customer)')
                                     : BukuBesarController::getAkunId('Pendapatan Jasa Percetakan');

                        $ketLawan = in_array($pesan->status_operasional, ['proses_pengantaran', 'selesai'])
                                     ? "Pelunasan Piutang Pesanan #{$pesan->id_pesan}"
                                     : "Pendapatan Penjualan Pesanan #{$pesan->id_pesan}";

                        BukuBesarController::catatJurnal($akunKas, $pembayaranPending->id_pembayaran, 'pendapatan', "Penerimaan Pembayaran Gateway Pesanan #{$pesan->id_pesan}", $nominalDibayar, 0);
                        BukuBesarController::catatJurnal($akunLawan, $pembayaranPending->id_pembayaran, 'pendapatan', $ketLawan, 0, $nominalDibayar);
                        event(new ProduksiBaruEvent($pesan));
                    }
                    // 👇 JIKA DARI POSTMAN (MANUAL), BIKIN BARU PAKE SERVICE LU 👇
                    else {
                        PembayaranService::catatPembayaranGateway(
                            $pesan,
                            $nominalDibayar,
                            'qris',
                            'qris_komerce',
                            $identifier
                        );
                    }

                    // Hitung Ulang Total Dibayar
                    $totalTelahDibayar = Pembayaran::where('id_pesan', $pesan->id_pesan)
                                                   ->where('status_pembayaran', 'berhasil')
                                                   ->sum('nominal_bayar');

                    // Toleransi selisih karena kode unik Komerce (150 perak)
                    $selisih = abs($totalTagihan - $totalTelahDibayar);
                    $selisihMurni = abs($tagihanTanpaKodeUnik - $totalTelahDibayar);

                    if ($selisih <= 150 || $selisihMurni <= 150 || $totalTelahDibayar >= $totalTagihan) {
                        $statusPembayaranBaru = 'lunas';
                    } else {
                        $statusPembayaranBaru = 'dibayar_sebagian';
                    }

                    if (in_array($statusPembayaranBaru, ['dibayar_sebagian', 'lunas']) && is_null($pesan->waktu_deadline)) {
                        $pesan->waktu_deadline = PesanService::hitungDeadlineKerja($pesan->pesananItem);
                    }

                    $pesan->status_pembayaran = $statusPembayaranBaru;
                    $pesan->save();

                    $dataBaru = PesanService::getSnapshotPesanan($pesan->id_pesan);

                    $labelStatus = str_replace('_', ' ', $statusPembayaranBaru);
                    $keteranganLog = "Sistem mengupdate status pembayaran menjadi: {$labelStatus} (via QRIS Komerce) (Nominal: Rp " . number_format($nominalDibayar, 0, ',', '.') . ")";

                    PesanService::catatLog(
                        $pesan->id_pesan,
                        'pembayaran_otomatis',
                        $keteranganLog,
                        $dataLama,
                        $dataBaru
                    );
                }
            } else {
                Log::warning('Webhook Komerce Gagal: Pesanan dengan identifier ' . $identifier . ' tidak ditemukan di database.');
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook berhasil diproses']);
    }
}
