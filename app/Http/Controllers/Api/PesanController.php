<?php

namespace App\Http\Controllers\Api;

use App\Events\PesananBaruEvent;
use App\Http\Controllers\Controller;
use App\Models\Komposisi;
use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemFinishing;
use App\Models\SkuFinishing;
use App\Services\PesanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PesanController extends Controller
{
    public function getCart(Request $request)
    {
        $customerId = $request->user()?->customer?->id_customer;

        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan.'], 404);
        }

        $cart = Pesan::with(['pesananItem.pesananItemFinishing', 'pembayaran'])
            ->where('id_customer', $customerId)
            ->where('status_operasional', 'keranjang')
            ->latest()
            ->first();

        if ($cart) {
            $rincian = PesanService::kalkulasiRincianPesanan($cart);
            $cart->total_tagihan = $rincian['grand_total'];
            $cart->total_dibayar = $rincian['total_dibayar'];
            $cart->sisa_tagihan  = $rincian['sisa_tagihan'];
        }

        return response()->json([
            'success' => true,
            'message' => $cart ? 'Data keranjang berhasil diambil.' : 'Keranjang masih kosong.',
            'data' => $cart
        ], 200);
    }

    public function addCart(Request $request)
    {
        $customerId = $request->user()?->customer?->id_customer;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'id_alamat' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.id_sku' => 'required|exists:produk_sku,id_sku',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.nama_produk_snapshot' => 'required|string',
            'items.*.harga_satuan_snapshot' => 'required|numeric',
            'items.*.harga_dasar_awal_snapshot' => 'nullable|numeric',
            'items.*.total_diskon_snapshot' => 'nullable|numeric',
            'items.*.rincian_diskon_snapshot' => 'nullable|array',
            'items.*.atribut_custom_snapshot' => 'nullable', // <-- Divalidasi
            'items.*.file_desain' => 'nullable',
            'items.*.tipe_file' => 'nullable|string|in:upload,link,email',
            'items.*.link_file' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Pesan::where('id_customer', $customerId)
                ->where('status_operasional', 'keranjang')
                ->first();

            if (!$pesanan) {
                $pesanan = Pesan::create([
                    'id_pesan' => PesanService::generateId(),
                    'kode_transaksi' => PesanService::generateKodeTransaksi(),
                    'id_customer' => $customerId,
                    'id_alamat' => $request->id_alamat,
                    'status_operasional' => 'keranjang',
                    'status_pembayaran' => 'belum_lunas',
                ]);
            }

            $id_pesan = $pesanan->id_pesan;
            $kode_transaksi = $pesanan->kode_transaksi;

            foreach ($request->items as $index => $item) {
                // Handle Finishings
                $finishings = $item['finishings'] ?? [];
                if (is_string($finishings)) {
                    $finishings = json_decode($finishings, true);
                }

                $selectedFinishingIds = [];
                if (!empty($finishings) && is_array($finishings)) {
                    foreach ($finishings as $fin) {
                        $skuFin = SkuFinishing::find($fin['id_sku_finishing']);
                        if ($skuFin && $skuFin->id_pilihan_finishing) {
                            $selectedFinishingIds[] = $skuFin->id_pilihan_finishing;
                        }
                    }
                }

                // Handle Custom Attributes (Jumlah Halaman, dll)
                $atributCustom = $item['atribut_custom_snapshot'] ?? null;
                if (is_string($atributCustom)) {
                    $atributCustom = json_decode($atributCustom, true);
                }

                $totalBeratItem = PesanService::hitungBeratTotalItem($item['id_sku'], $item['jumlah'], $selectedFinishingIds);

                $fileDesainData = null;

                if ($request->hasFile("items.{$index}.file_desain")) {
                    $file = $request->file("items.{$index}.file_desain");
                    if (is_array($file)) $file = $file[0];

                    $filename = "{$id_pesan}-" . \Illuminate\Support\Str::random(6) . "." . $file->getClientOriginalExtension();
                    $path = $file->storeAs('desain_pesanan', $filename, 'public');

                    $fileDesainData = [
                        'tipe' => 'upload',
                        'nilai' => $path
                    ];
                } elseif (isset($item['tipe_file']) && $item['tipe_file'] === 'link') {
                    $fileDesainData = [
                        'tipe' => 'link',
                        'nilai' => $item['link_file'] ?? ''
                    ];
                } elseif (isset($item['tipe_file']) && $item['tipe_file'] === 'email') {
                    $fileDesainData = [
                        'tipe' => 'email',
                        'nilai' => 'Akan dikirim oleh customer melalui Email.'
                    ];
                }

                $pesananItem = PesananItem::create([
                    'id_pesan' => $id_pesan,
                    'kode_transaksi' => $kode_transaksi,
                    'id_sku' => $item['id_sku'],
                    'nama_produk_snapshot' => $item['nama_produk_snapshot'],
                    'jumlah' => $item['jumlah'],

                    'harga_satuan_snapshot' => $item['harga_satuan_snapshot'],
                    'harga_dasar_awal_snapshot' => $item['harga_dasar_awal_snapshot'] ?? $item['harga_satuan_snapshot'],
                    'total_diskon_snapshot' => $item['total_diskon_snapshot'] ?? 0,
                    'rincian_diskon_snapshot' => $item['rincian_diskon_snapshot'] ?? null,

                    'estimasi_pengerjaan_snapshot' => $item['estimasi_pengerjaan'] ?? 'Reguler',
                    'harga_pengerjaan_snapshot' => $item['harga_pengerjaan_snapshot'] ?? 0,
                    'total_berat_snapshot' => $totalBeratItem,

                    'file_desain' => $fileDesainData,
                    'catatan' => $item['catatan'] ?? null,

                    'atribut_custom_snapshot' => $atributCustom // <-- Eksekusi masuk database
                ]);

                if (!empty($finishings) && is_array($finishings)) {
                    foreach ($finishings as $finishing) {
                        $skuFinishing = SkuFinishing::with('pilihanFinishing.finishing')->find($finishing['id_sku_finishing']);

                        if ($skuFinishing) {
                            $namaFinishing = strtoupper($skuFinishing->pilihanFinishing->finishing->nama_finishing);
                            $namaPilihan = $skuFinishing->pilihanFinishing->nama_pilihan;

                            PesananItemFinishing::create([
                                'id_pesanan_item' => $pesananItem->id,
                                'id_sku_finishing' => $finishing['id_sku_finishing'],
                                'nama_finishing_snapshot' => $namaFinishing . ': ' . $namaPilihan,
                                'harga_finishing_snapshot' => $finishing['harga_finishing_snapshot'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan ke keranjang!',
                'data' => ['id_pesan' => $id_pesan]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Add To Cart API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan ke keranjang.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCart(Request $request, $id)
    {
        $customerId = $request->user()?->customer?->id_customer;

        $request->validate(['jumlah' => 'required|integer|min:1']);

        try {
            $item = PesananItem::with('pesananItemFinishing')
                ->where('id', $id)
                ->whereHas('pesan', function ($query) use ($customerId) {
                    $query->where('id_customer', $customerId)
                        ->where('status_operasional', 'keranjang');
                })
                ->firstOrFail();

            $selectedFinishingIds = [];
            $minQtyRequired = 1;

            foreach ($item->pesananItemFinishing as $fin) {
                $skuFinishing = SkuFinishing::find($fin->id_sku_finishing);

                if ($skuFinishing) {
                    if ($skuFinishing->id_pilihan_finishing) {
                        $selectedFinishingIds[] = $skuFinishing->id_pilihan_finishing;
                    }

                    if ($skuFinishing->minimum_pesan > $minQtyRequired) {
                        $minQtyRequired = $skuFinishing->minimum_pesan;
                    }
                }
            }

            if ($request->jumlah < $minQtyRequired) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui. Minimum pemesanan untuk kombinasi jasa finishing ini adalah ' . $minQtyRequired . ' pcs.'
                ], 422);
            }

            $beratBaru = PesanService::hitungBeratTotalItem($item->id_sku, $request->jumlah, $selectedFinishingIds);

            $item->update([
                'jumlah' => $request->jumlah,
                'total_berat_snapshot' => $beratBaru
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jumlah berhasil diperbarui.',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jumlah.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroyCart(Request $request, $id)
    {
        $customerId = $request->user()?->customer?->id_customer;

        $item = PesananItem::where('id', $id)
            ->whereHas('pesan', function ($query) use ($customerId) {
                $query->where('id_customer', $customerId)
                    ->where('status_operasional', 'keranjang');
            })
            ->firstOrFail();

        PesananItemFinishing::where('id_pesanan_item', $item->id)->delete();
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus.'
        ]);
    }

    public function checkoutCart(Request $request)
    {
        $customerId = $request->user()?->customer?->id_customer;

        $request->validate([
            'id_alamat' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*' => 'integer',
            'ekspedisi_nama' => 'required|string',
            'ekspedisi_layanan' => 'required|string',
            'harga_ongkir' => 'required|numeric|min:0',
            'ekspedisi_estimasi' => 'required|string',
            'kode_voucher' => 'nullable|string',
            'diskon_voucher_nominal' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $cart = Pesan::where('id_customer', $customerId)
                ->where('status_operasional', 'keranjang')
                ->firstOrFail();

            $selectedItems = PesananItem::with('pesananItemFinishing')
                ->whereIn('id', $request->items)
                ->where('id_pesan', $cart->id_pesan)
                ->get();

            if ($selectedItems->isEmpty()) {
                throw new \Exception('Tidak ada item yang dipilih.');
            }

            $newPesan = Pesan::create([
                'id_pesan' => PesanService::generateId(),
                'kode_transaksi' => PesanService::generateKodeTransaksi(),
                'id_customer' => $customerId,
                'id_alamat' => $request->id_alamat,
                'status_operasional' => 'menunggu_diproses',
                'status_pembayaran' => 'belum_lunas',
                'ekspedisi_nama' => $request->ekspedisi_nama,
                'ekspedisi_layanan' => $request->ekspedisi_layanan,
                'harga_ongkir' => $request->harga_ongkir,
                'ekspedisi_estimasi' => $request->ekspedisi_estimasi,
                'kode_voucher' => $request->kode_voucher ?? null,
                'diskon_voucher_nominal' => $request->diskon_voucher_nominal ?? 0,
            ]);

            foreach ($selectedItems as $item) {
                $hppSatuan = Komposisi::where('id_sku', $item->id_sku)
                    ->whereNull('id_pilihan_finishing')
                    ->sum('hpp');

                $selectedFinishingIds = [];
                foreach ($item->pesananItemFinishing as $finishing) {
                    $skuFinishingAsli = SkuFinishing::find($finishing->id_sku_finishing);
                    $idPilihanFinishing = $skuFinishingAsli ? $skuFinishingAsli->id_pilihan_finishing : $finishing->id_sku_finishing;

                    if ($idPilihanFinishing) {
                        $selectedFinishingIds[] = $idPilihanFinishing;
                    }
                }

                $totalBeratItem = PesanService::hitungBeratTotalItem($item->id_sku, $item->jumlah, $selectedFinishingIds);

                $item->update([
                    'id_pesan'            => $newPesan->id_pesan,
                    'hpp_satuan_snapshot' => $hppSatuan,
                    'total_berat_snapshot'=> $totalBeratItem
                ]);

                foreach ($item->pesananItemFinishing as $finishing) {
                    $skuFinishingAsli = SkuFinishing::find($finishing->id_sku_finishing);
                    $idPilihanFinishing = $skuFinishingAsli ? $skuFinishingAsli->id_pilihan_finishing : $finishing->id_sku_finishing;

                    $hppFinishing = Komposisi::where('id_sku', $item->id_sku)
                        ->where('id_pilihan_finishing', $idPilihanFinishing)
                        ->sum('hpp');

                    $finishing->update([
                        'hpp_finishing_snapshot' => $hppFinishing
                    ]);
                }
            }

            $newPesan->load([
                'customer.user',
                'pesananItem.pesananItemFinishing',
                'pembayaran'
            ]);

            $rincian = PesanService::kalkulasiRincianPesanan($newPesan);
            $totalTagihanLengkap = $rincian['grand_total'];

            DB::commit();

            try {
                $rekening = [
                    'bank' => env('BANK_NAME'),
                    'nomor' => env('BANK_NUMBER'),
                    'atas_nama' => env('BANK_OWNER'),
                ];

                PesanService::kirimNotifikasiCheckout(
                    $newPesan,
                    $rincian['subtotal'],
                    $rincian['kode_unik'],
                    $rekening
                );

                event(new PesananBaruEvent($newPesan));

            } catch (\Exception $ex) {
                Log::warning('Checkout sukses, tapi notifikasi/websocket gagal: ' . $ex->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil.',
                'data' => [
                    'id_pesan' => $newPesan->id_pesan,
                    'kode_transaksi' => $newPesan->kode_transaksi,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Checkout API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Checkout gagal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPesanan(Request $request)
    {
        $customerId = $request->user()?->customer?->id_customer;

        $pesanan = Pesan::with(['alamat', 'pesananItem.pesananItemFinishing', 'pembayaran'])
            ->where('id_customer', $customerId)
            ->where('status_operasional', '!=', 'keranjang')
            ->latest()
            ->get()
            ->map(function ($pesan) {
                $rincian = PesanService::kalkulasiRincianPesanan($pesan);
                $pesan->total_tagihan = $rincian['grand_total'];
                $pesan->total_dibayar = $rincian['total_dibayar'];
                $pesan->sisa_tagihan  = $rincian['sisa_tagihan'];
                return $pesan;
            });

        return response()->json([
            'success' => true,
            'data' => $pesanan
        ]);
    }

    public function getPesananByKodeTransaksi(Request $request, string $kode_transaksi)
    {
        $customerId = $request->user()?->customer?->id_customer;

        $pesanan = Pesan::with(['alamat', 'pesananItem.pesananItemFinishing', 'pembayaran'])
            ->where('id_customer', $customerId)
            ->where('kode_transaksi', $kode_transaksi)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.'
            ], 404);
        }

        $rincian = PesanService::kalkulasiRincianPesanan($pesanan);
        $pesanan->kode_unik = $rincian['kode_unik'];
        $pesanan->total_tagihan = $rincian['grand_total'];
        $pesanan->total_dibayar = $rincian['total_dibayar'];
        $pesanan->sisa_tagihan  = $rincian['sisa_tagihan'];

        return response()->json([
            'success' => true,
            'data' => $pesanan
        ]);
    }

    public function getStatusPesanan(string $kode_transaksi)
    {
        $pesanan = Pesan::with([
            'alamat',
            'pesananItem.pesananItemFinishing',
            'pembayaran'
        ])
        ->where('kode_transaksi', $kode_transaksi)
        ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.'
            ], 404);
        }

        $rincian = PesanService::kalkulasiRincianPesanan($pesanan);

        $pesanan->kode_unik = $rincian['kode_unik'];
        $pesanan->total_tagihan = $rincian['grand_total'];
        $pesanan->total_dibayar = $rincian['total_dibayar'];
        $pesanan->sisa_tagihan = $rincian['sisa_tagihan'];

        return response()->json([
            'success' => true,
            'data' => $pesanan
        ]);
    }

    public function pesananDiterimaPelanggan(Request $request, $id_pesan)
    {
        try {
            $customerId = $request->user()?->customer?->id_customer;

            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Customer tidak valid.'
                ], 401);
            }

            $pesanan = Pesan::where('id_pesan', $id_pesan)
                            ->where('id_customer', $customerId)
                            ->first();

            if (!$pesanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan atau bukan milik Anda.'
                ], 404);
            }

            if ($pesanan->status_operasional !== 'proses_pengantaran') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dapat diselesaikan karena status saat ini adalah: ' . str_replace('_', ' ', $pesanan->status_operasional)
                ], 400);
            }

            $pesanan->status_operasional = 'selesai';
            $pesanan->tanggal_selesai = now();
            $pesanan->save();

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! Pesanan berhasil diselesaikan.',
                'data' => $pesanan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server saat memproses data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
