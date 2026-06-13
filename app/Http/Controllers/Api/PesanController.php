<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesan;
use App\Models\PesananItem;
use App\Models\PesananItemFinishing;
use App\Models\SkuFinishing;
use App\Services\PesanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PesanController extends Controller
{
    public function getCart(Request $request)
    {
        $customerId = $request->user()?->customer?->id_customer;

        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan.'], 404);
        }

        $cart = Pesan::with(['pesananItem.pesananItemFinishing'])
            ->where('id_customer', $customerId)
            ->where('status_operasional', 'keranjang')
            ->latest()
            ->first();

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
            'items.*.file_desain' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:20480',
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Pesan::where('id_customer', $customerId)
                ->where('status_operasional', 'keranjang')
                ->first();

            if (!$pesanan) {
                $pesanan = Pesan::create([
                    'id_pesan' => PesanService::generateId(),
                    'id_customer' => $customerId,
                    'id_alamat' => $request->id_alamat,
                    'status_operasional' => 'keranjang',
                    'status_pembayaran' => 'belum_lunas',
                ]);
            }

            $id_pesan = $pesanan->id_pesan;

            foreach ($request->items as $index => $item) {
                $filePath = null;
                if ($request->hasFile("items.{$index}.file_desain")) {
                    $file = $request->file("items.{$index}.file_desain");
                    $filename = $id_pesan . '_cart_' . $index . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('desain_pesanan', $filename, 'public');
                }

                $finishings = $item['finishings'] ?? [];
                if (is_string($finishings)) {
                    $finishings = json_decode($finishings, true);
                }

                $newFinishingIds = collect($finishings)
                    ->pluck('id_sku_finishing')
                    ->sort()
                    ->values()
                    ->toArray();

                $existingItems = PesananItem::with('pesananItemFinishing')
                    ->where('id_pesan', $id_pesan)
                    ->where('id_sku', $item['id_sku'])
                    ->get();

                $matchedItem = null;

                foreach ($existingItems as $existingItem) {
                    $existingFinishingIds = $existingItem
                        ->pesananItemFinishing
                        ->pluck('id_sku_finishing')
                        ->sort()
                        ->values()
                        ->toArray();

                    if ($existingFinishingIds == $newFinishingIds) {
                        $matchedItem = $existingItem;
                        break;
                    }
                }

                if ($matchedItem) {
                    $matchedItem->increment(
                        'jumlah',
                        $item['jumlah']
                    );
                    continue;
                }

                $pesananItem = PesananItem::create([
                    'id_pesan' => $id_pesan,
                    'id_sku' => $item['id_sku'],
                    'nama_produk_snapshot' => $item['nama_produk_snapshot'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan_snapshot' => $item['harga_satuan_snapshot'],
                    'estimasi_pengerjaan_snapshot' => $item['estimasi_pengerjaan'] ?? 'Reguler',
                    'harga_pengerjaan_snapshot' => $item['harga_pengerjaan_snapshot'] ?? 0,
                    'file_desain' => $filePath,
                    'catatan' => $item['catatan'] ?? null,
                ]);

                if (!empty($finishings) && is_array($finishings)) {
                    foreach ($finishings as $finishing) {

                    $skuFinishing = SkuFinishing::with(
                        'pilihanFinishing.finishing'
                    )->find($finishing['id_sku_finishing']);

                    $namaFinishing =
                        strtoupper(
                            $skuFinishing->pilihanFinishing->finishing->nama_finishing
                        );

                    $namaPilihan =
                        $skuFinishing->pilihanFinishing->nama_pilihan;

                    PesananItemFinishing::create([
                        'id_pesanan_item' => $pesananItem->id,
                        'id_sku_finishing' => $finishing['id_sku_finishing'],
                        'nama_finishing_snapshot' =>
                            $namaFinishing . ': ' . $namaPilihan,
                        'harga_finishing_snapshot' =>
                            $finishing['harga_finishing_snapshot'],
                    ]);
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

        $request->validate([
            'jumlah' => 'required|integer|min:1'
        ]);

        try {
            $item = PesananItem::where('id', $id)
                ->whereHas('pesan', function ($query) use ($customerId) {
                    $query->where('id_customer', $customerId)
                        ->where('status_operasional', 'keranjang');
                })
                ->firstOrFail();

            $item->update([
                'jumlah' => $request->jumlah
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

        PesananItemFinishing::where(
            'id_pesanan_item',
            $item->id
        )->delete();

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus.'
        ]);
    }
}
