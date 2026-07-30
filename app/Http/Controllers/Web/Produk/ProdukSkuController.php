<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Finishing;
use App\Models\PilihanFinishing;
use App\Models\ProdukSku;
use App\Models\RoleCustomer;
use App\Models\SkuDetailPilihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProdukSkuController extends Controller
{
    public function syncSku(Request $request, $id_produk)
    {
        $request->validate([
            'skus' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $incomingSkus = collect($request->skus ?? []);
            $incomingIds = $incomingSkus->pluck('id_sku')->filter()->toArray();

            ProdukSku::where('id_produk', $id_produk)
                ->whereNotIn('id_sku', $incomingIds)
                ->delete();

            $lastSku = ProdukSku::where('id_produk', $id_produk)
                ->orderByRaw('LENGTH(id_sku) DESC')
                ->orderBy('id_sku', 'desc')
                ->first();

            $lastIndex = 0;
            if ($lastSku) {
                $parts = explode('-SKU-', $lastSku->id_sku);
                if (count($parts) == 2) {
                    $lastIndex = (int) $parts[1];
                }
            }

            if (!empty($request->skus)) {
                foreach ($request->skus as $data) {

                    if (!empty($data['id_sku'])) {
                        $sku = ProdukSku::where('id_sku', $data['id_sku'])->first();
                        if ($sku) {
                            $sku->update([
                                'nama_sku' => $data['nama_sku'],
                                'minimum_pesan' => $data['minimum_pesan'],
                                'harga' => $data['harga']
                            ]);

                            SkuDetailPilihan::where('id_sku', $sku->id_sku)->delete();
                            foreach ($data['pilihan_ids'] as $id_pilihan) {
                                SkuDetailPilihan::create([
                                    'id_sku' => $sku->id_sku,
                                    'id_pilihan' => $id_pilihan
                                ]);
                            }
                        }
                    } else {
                        $lastIndex++;
                        $skuId = $id_produk . "-SKU-" . str_pad($lastIndex, 3, '0', STR_PAD_LEFT);

                        ProdukSku::create([
                            'id_sku' => $skuId,
                            'id_produk' => $id_produk,
                            'nama_sku' => $data['nama_sku'],
                            'minimum_pesan' => $data['minimum_pesan'],
                            'harga' => $data['harga']
                        ]);

                        foreach ($data['pilihan_ids'] as $id_pilihan) {
                            SkuDetailPilihan::create([
                                'id_sku' => $skuId,
                                'id_pilihan' => $id_pilihan
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'SKU Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function importCsv(Request $request, $id_produk)
    {
        $request->validate([
            'skala_import' => 'required|in:produk_ini,semua_produk', // Validasi input baru
            'tipe_import'  => 'required|in:sku_finishing,harga_bertingkat,harga_pengerjaan,diskon_customer,komposisi',
            'file_csv'     => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file_csv');
        $csvData = array_map('str_getcsv', file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        if (count($csvData) < 2) {
            return back()->withErrors(['message' => 'File CSV kosong atau tidak memiliki baris data.']);
        }

        $headers = array_map('trim', $csvData[0]);
        $headers[0] = preg_replace('/[\xef\xbb\xbf]/', '', $headers[0]);

        $rows = array_slice($csvData, 1);
        $insertData = [];
        $skusAffected = [];
        $now = Carbon::now();

        $validSkus = [];
        if ($request->skala_import === 'produk_ini') {
            $validSkus = DB::table('produk_sku')->where('id_produk', $id_produk)->pluck('id_sku')->toArray();
        }

        foreach ($rows as $row) {
            if (count($row) !== count($headers) || empty(array_filter($row))) {
                continue;
            }

            $row = array_map('trim', $row);
            $rowData = array_combine($headers, $row);

            if (empty($rowData['id_sku'])) {
                continue;
            }

            if ($request->skala_import === 'produk_ini') {
                if (!in_array($rowData['id_sku'], $validSkus)) {
                    continue;
                }
            }

            $rowData['created_at'] = $now;
            $rowData['updated_at'] = $now;

            if ($request->tipe_import === 'komposisi') {
                if (empty($rowData['id_pilihan_finishing'])) {
                    $rowData['id_pilihan_finishing'] = null;
                }
            }

            if ($request->tipe_import === 'harga_bertingkat') {
                if ($rowData['max'] === '') {
                    $rowData['max'] = 0;
                }
            }
            if ($request->tipe_import === 'sku_finishing') {
                if ($rowData['harga_tambahan'] === '') {
                    $rowData['harga_tambahan'] = 0;
                }
                if ($rowData['minimum_pesan'] === '') {
                    $rowData['minimum_pesan'] = 1;
                }
            }

            $insertData[] = $rowData;

            if (!in_array($rowData['id_sku'], $skusAffected)) {
                $skusAffected[] = $rowData['id_sku'];
            }
        }

        if (empty($insertData)) {
            return back()->withErrors(['message' => 'Tidak ada data valid yang sesuai dengan pilihan skala import.']);
        }

        try {
            DB::beginTransaction();
            DB::table($request->tipe_import)->whereIn('id_sku', $skusAffected)->delete();

            $chunks = array_chunk($insertData, 500);
            foreach ($chunks as $chunk) {
                DB::table($request->tipe_import)->insert($chunk);
            }

            DB::commit();

            $pesan = $request->skala_import === 'produk_ini'
                ? 'Data CSV berhasil di-import hanya untuk produk ini!'
                : 'Data CSV berhasil di-import untuk semua produk!';

            return back()->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Terjadi kesalahan sistem saat menyimpan ke database: ' . $e->getMessage()]);
        }
    }

    public function finishing($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'skuFinishing.pilihanFinishing'])->findOrFail($id_sku);
        $finishings = Finishing::with('pilihanFinishing')->get();

        return Inertia::render('Produk/FormFinishing', [
            'sku' => $sku,
            'finishings' => $finishings,
        ]);
    }

    public function hargaBertingkat($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'hargaBertingkat'])->findOrFail($id_sku);

        return Inertia::render('Produk/FormHargaBertingkat', [
            'sku' => $sku
        ]);
    }

    public function hargaPengerjaan($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'hargaPengerjaan'])->findOrFail($id_sku);

        return Inertia::render('Produk/FormHargaPengerjaan', [
            'sku' => $sku
        ]);
    }

    public function diskonCustomer($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'diskonCustomer'])->findOrFail($id_sku);
        $roles = RoleCustomer::all();

        return Inertia::render('Produk/FormDiskonCustomer', [
            'sku' => $sku,
            'roles' => $roles
        ]);
    }

    public function komposisi($id_sku)
    {
        $sku = ProdukSku::with(['produk', 'komposisi.bahanBaku'])->findOrFail($id_sku);
        $bahan_baku = BahanBaku::where('is_active', true)->get();

        $pilihan_finishing = PilihanFinishing::all();

        return Inertia::render('Produk/FormKomposisi', [
            'sku' => $sku,
            'bahan_baku' => $bahan_baku,
            'pilihan_finishing' => $pilihan_finishing,
        ]);
    }

    public function destroy($id_sku)
    {
        try {
            DB::beginTransaction();

            $sku = ProdukSku::findOrFail($id_sku);
            $sku->delete();

            DB::commit();
            return redirect()->back()->with('success', 'SKU dan seluruh konfigurasi harganya berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus SKU: ' . $e->getMessage());
        }
    }
}
