<?php

namespace App\Http\Controllers;

use App\Models\PembelianBahan;
use App\Models\DetailPembelian;
use App\Models\BahanBaku;
use App\Models\Komposisi;
use App\Models\BukuBesar;
use App\Http\Controllers\BukuBesarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianBahanController extends Controller
{
    public function index()
    {
        return inertia('PembelianBahan/Index', [
            'pembelian' => PembelianBahan::with('staf.user')->latest()->get()
        ]);
    }

    public function create()
    {
        return inertia('PembelianBahan/Form', [
            'bahan_baku' => BahanBaku::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_beli' => 'required|date',
            'nama_supplier' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'details.*.jumlah' => 'required|numeric|min:1',
            'details.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $prefix = 'PB-' . date('ym') . '-';
            $latest = PembelianBahan::where('id_pembelian', 'like', $prefix . '%')
                                    ->orderBy('id_pembelian', 'desc')
                                    ->first();

            $number = $latest ? (int) substr($latest->id_pembelian, -4) + 1 : 1;
            $id_pembelian = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

            $total_biaya = collect($request->details)->sum(function ($item) {
                return $item['jumlah'] * $item['harga_satuan'];
            });

            PembelianBahan::create([
                'id_pembelian' => $id_pembelian,
                'tanggal_beli' => $request->tanggal_beli,
                'nama_supplier' => $request->nama_supplier,
                'total_biaya' => $total_biaya,
                'id_staf' => auth()->user()->staf?->id_staf ?? 'STAF-20260514-001',
            ]);

            foreach ($request->details as $item) {
                DetailPembelian::create([
                    'id_pembelian' => $id_pembelian,
                    'id_bahan_baku' => $item['id_bahan_baku'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                ]);

                $bahan = BahanBaku::findOrFail($item['id_bahan_baku']);
                $bahan->stok_sekarang += $item['jumlah'];
                $bahan->harga_beli = $item['harga_satuan'];
                $bahan->save();

                Komposisi::where('id_bahan_baku', $bahan->id_bahan_baku)
                    ->get()
                    ->each(function ($komposisi) use ($bahan) {
                        $komposisi->update([
                            'hpp' => $komposisi->jumlah_pakai * $bahan->harga_beli
                        ]);
                    });
            }

            $akun_persediaan = BukuBesarController::getAkunId('Persediaan Bahan Baku');
            $akun_kas = BukuBesarController::getAkunId('Kas Tunai');

            BukuBesarController::catatJurnal(
                $akun_persediaan,
                $id_pembelian,
                'pembelian',
                "Pembelian Bahan Baku dari Supplier: " . $request->nama_supplier,
                $total_biaya,
                0
            );

            BukuBesarController::catatJurnal(
                $akun_kas,
                $id_pembelian,
                'pembelian',
                "Pembayaran Pembelian Bahan Baku #" . $id_pembelian,
                0,
                $total_biaya
            );

            DB::commit();
            return redirect()->route('pembelian-bahan.index')->with('success', 'Transaksi pembelian berhasil disimpan, stok, HPP resep, dan Jurnal telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $pembelian = PembelianBahan::with('detailPembelian.bahanBaku')->findOrFail($id);
        return inertia('PembelianBahan/Form', [
            'pembelian' => $pembelian,
            'bahan_baku' => BahanBaku::where('is_active', true)->get()
        ]);
    }

    public function update(Request $request, $id)
    {
        $pembelian = PembelianBahan::findOrFail($id);

        $request->validate([
            'tanggal_beli' => 'required|date',
            'nama_supplier' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'details.*.jumlah' => 'required|numeric|min:1',
            'details.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $oldDetails = DetailPembelian::where('id_pembelian', $id)->get();
            foreach ($oldDetails as $od) {
                $bahanOld = BahanBaku::find($od->id_bahan_baku);
                if ($bahanOld) {
                    $bahanOld->stok_sekarang -= $od->jumlah;
                    $bahanOld->save();
                }
            }

            DetailPembelian::where('id_pembelian', $id)->delete();

            $total_biaya = collect($request->details)->sum(function ($item) {
                return $item['jumlah'] * $item['harga_satuan'];
            });

            $pembelian->update([
                'tanggal_beli' => $request->tanggal_beli,
                'nama_supplier' => $request->nama_supplier,
                'total_biaya' => $total_biaya,
            ]);

            foreach ($request->details as $item) {
                DetailPembelian::create([
                    'id_pembelian' => $id,
                    'id_bahan_baku' => $item['id_bahan_baku'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                ]);

                $bahanNew = BahanBaku::findOrFail($item['id_bahan_baku']);
                $bahanNew->stok_sekarang += $item['jumlah'];
                $bahanNew->harga_beli = $item['harga_satuan'];
                $bahanNew->save();

                Komposisi::where('id_bahan_baku', $bahanNew->id_bahan_baku)
                    ->get()
                    ->each(function ($komposisi) use ($bahanNew) {
                        $komposisi->update([
                            'hpp' => $komposisi->jumlah_pakai * $bahanNew->harga_beli
                        ]);
                    });
            }

            BukuBesar::where('id_referensi', $id)->where('tipe_referensi', 'pembelian')->delete();

            $akun_persediaan = BukuBesarController::getAkunId('Persediaan Bahan Baku');
            $akun_kas = BukuBesarController::getAkunId('Kas Tunai');

            BukuBesarController::catatJurnal(
                $akun_persediaan,
                $id,
                'pembelian',
                "Revisi Pembelian Bahan Baku dari Supplier: " . $request->nama_supplier,
                $total_biaya,
                0
            );

            BukuBesarController::catatJurnal(
                $akun_kas,
                $id,
                'pembelian',
                "Revisi Pembayaran Pembelian Bahan Baku #" . $id,
                0,
                $total_biaya
            );

            DB::commit();
            return redirect()->route('pembelian-bahan.index')->with('success', 'Transaksi pembelian berhasil diperbarui dan jurnal disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update transaksi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $pembelian = PembelianBahan::findOrFail($id);

            foreach ($pembelian->detailPembelian as $detail) {
                $bahan = BahanBaku::find($detail->id_bahan_baku);
                if ($bahan) {
                    $bahan->stok_sekarang -= $detail->jumlah;

                    $pembelianSebelumnya = DetailPembelian::where('id_bahan_baku', $detail->id_bahan_baku)
                        ->where('id_pembelian', '!=', $id)
                        ->latest()
                        ->first();

                    if ($pembelianSebelumnya) {
                        $bahan->harga_beli = $pembelianSebelumnya->harga_satuan;
                    }

                    $bahan->save();

                    Komposisi::where('id_bahan_baku', $bahan->id_bahan_baku)
                        ->get()
                        ->each(function ($komposisi) use ($bahan) {
                            $komposisi->update([
                                'hpp' => $komposisi->jumlah_pakai * $bahan->harga_beli
                            ]);
                        });
                }
            }

            $pembelian->delete();

            BukuBesar::where('id_referensi', $id)->where('tipe_referensi', 'pembelian')->delete();

            DB::commit();
            return back()->with('success', 'Transaksi dihapus, stok dikembalikan, dan Jurnal dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
