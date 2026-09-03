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
use Illuminate\Support\Facades\Storage;
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
                            // Update data SKU (Harga hanya jadi pajangan/mulai dari)
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

    public function editSku($id_sku)
    {
        $sku = ProdukSku::with(['produk'])->findOrFail($id_sku);

        return inertia('Produk/EditSku', [
            'sku' => $sku,
            'produk' => $sku->produk
        ]);
    }

    public function updateSku(Request $request, $id_sku)
    {
        $request->validate([
            'nama_sku' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe_kalkulasi' => 'required|in:standard,cetak_meteran,cetak_buku',
            'satuan' => 'nullable|string|max:50',
            'minimum_pesan' => 'required|numeric|min:1',
            'kelipatan_pesan' => 'required|numeric|min:1',
            'harga' => 'required|numeric|min:0',
            'harga_tambahan_dimensi' => 'required|numeric|min:0',
            'gambar' => 'nullable|array',
            'gambar.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        try {
            $sku = ProdukSku::findOrFail($id_sku);

            $dataUpdate = [
                'deskripsi' => $request->deskripsi,
                'tipe_kalkulasi' => $request->tipe_kalkulasi,
                'satuan' => $request->satuan,
                'minimum_pesan' => $request->minimum_pesan,
                'kelipatan_pesan' => $request->kelipatan_pesan,
                'harga' => $request->harga,
                'harga_tambahan_dimensi' => $request->harga_tambahan_dimensi,
            ];

            if ($request->hasFile('gambar')) {
                if ($sku->gambar) {
                    $oldImages = is_array($sku->gambar) ? $sku->gambar : json_decode($sku->gambar, true) ?? [$sku->gambar];
                    foreach ($oldImages as $oldImg) {
                        Storage::disk('public')->delete($oldImg);
                    }
                }

                $paths = [];
                foreach ($request->file('gambar') as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $paths[] = $file->storeAs('produk_sku_images', $filename, 'public');
                }

                $dataUpdate['gambar'] = $paths;
            }

            $sku->update($dataUpdate);

            return redirect()->back()->with('success', 'Data SKU berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function importCsv(Request $request, $id_produk)
    {
        $request->validate([
            'skala_import' => 'required|in:produk_ini,semua_produk',
            'tipe_import'  => 'required|in:sku_finishing,harga_bertingkat,diskon_customer,komposisi',
            'file_csv'     => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file_csv');
        $csvData = array_map('str_getcsv', file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        if (count($csvData) < 2) {
            return back()->withErrors(['message' => 'File CSV kosong atau tidak memiliki baris data.']);
        }

        $headers = array_map('trim', $csvData[0]);
        $headers[0] = preg_replace('/[\xef\xbb\xbf]/', '', $headers[0]); // Hapus karakter BOM

        // Normalisasi nama header utama agar toleran terhadap 'varian', 'id_sku', atau 'jumlah'
        $headers[0] = strtolower($headers[0]);
        if ($headers[0] === 'varian' || $headers[0] === 'sku') {
            $headers[0] = 'id_sku';
        }
        if (isset($headers[1])) {
            $headers[1] = strtolower($headers[1]);
        }

        $rows = array_slice($csvData, 1);

        $insertData = [];
        $skusAffected = [];
        $now = Carbon::now();

        $validSkus = [];
        if ($request->skala_import === 'produk_ini') {
            $validSkus = DB::table('produk_sku')->where('id_produk', $id_produk)->pluck('id_sku')->toArray();
        }

        $currentSku = null;
        $currentPilihanFinishing = null;
        $finishingGroups = [];

        // CONTAINER MATRIKS
        $matrixHargaBertingkat = [];
        $allSkuMins = []; // Menyimpan semua nilai Min per SKU buat backup perhitungan

        foreach ($rows as $row) {
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            if (empty(array_filter($row))) {
                continue;
            }

            $row = array_map('trim', $row);
            $rowData = array_combine($headers, $row);

            // 1. FILL-DOWN ID_SKU / VARIAN
            $skuKey = $headers[0];
            if (!empty($rowData[$skuKey])) {
                $currentSku = $rowData[$skuKey];
            } else {
                $rowData[$skuKey] = $currentSku;
            }

            if (empty($rowData[$skuKey])) {
                continue;
            }

            $skuFinal = $rowData[$skuKey];

            // 2. FILL-DOWN ID_PILIHAN_FINISHING
            if ($request->tipe_import === 'sku_finishing') {
                if (!empty($rowData['id_pilihan_finishing'])) {
                    $currentPilihanFinishing = $rowData['id_pilihan_finishing'];
                } else {
                    $rowData['id_pilihan_finishing'] = $currentPilihanFinishing;
                }
            }

            if ($request->skala_import === 'produk_ini' && !in_array($skuFinal, $validSkus)) {
                continue;
            }

            if (!in_array($skuFinal, $skusAffected)) {
                $skusAffected[] = $skuFinal;
            }

            // ==============================================================
            // LOGIC KHUSUS HARGA BERTINGKAT & SLA (BACA MIN - MAX EKSPLISIT)
            // ==============================================================
            if ($request->tipe_import === 'harga_bertingkat') {
                $jumlahKey = $headers[1];
                $jumlahRaw = strtolower(trim($rowData[$jumlahKey] ?? ''));

                if ($jumlahRaw === '') {
                    continue;
                }

                // Cerdas mengekstrak angka: "1 - 499 pcs" -> dapet [1, 499]
                preg_match_all('/\d+/', str_replace(['.', ','], '', $jumlahRaw), $matches);
                $minQty = isset($matches[0][0]) ? (int)$matches[0][0] : 0;

                if ($minQty <= 0) {
                    continue;
                }

                // Kalau user nulis format "X - Y", ambil Y sebagai max.
                // Kalau cuma "X pcs" (gak ada angka kedua), kasih nilai -1 buat flag auto-hitung.
                $maxQty = isset($matches[0][1]) ? (int)$matches[0][1] : -1;

                // Kumpulkan semua minQty dari SKU ini secara global
                if (!isset($allSkuMins[$skuFinal])) $allSkuMins[$skuFinal] = [];
                if (!in_array($minQty, $allSkuMins[$skuFinal])) {
                    $allSkuMins[$skuFinal][] = $minQty;
                }

                // Loop dinamis membaca Header SLA (10 hari, 12 hari, dll)
                foreach ($headers as $index => $headerName) {
                    if ($index < 2) continue;

                    $nilaiRaw = $rowData[$headerName] ?? '';
                    if ($nilaiRaw !== '' && $nilaiRaw !== '-') {
                        $nilaiBersih = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d.,]/', '', $nilaiRaw));

                        if ($nilaiBersih > 0) {
                            $matrixHargaBertingkat[$skuFinal][$headerName][] = [
                                'min'   => $minQty,
                                'max'   => $maxQty,
                                'nilai' => $nilaiBersih
                            ];
                        }
                    }
                }
                continue;
            }

            // ==============================================================
            // LOGIC FINISHING TABEL
            // ==============================================================
            $rowData['created_at'] = $now;
            $rowData['updated_at'] = $now;

            if ($request->tipe_import === 'sku_finishing') {
                $key = $skuFinal . '_' . ($rowData['id_pilihan_finishing'] ?? '');

                if (!isset($finishingGroups[$key])) {
                    $finishingGroups[$key] = [
                        'master' => null,
                        'tiers'  => []
                    ];
                }

                if (isset($rowData['harga_tambahan']) && $rowData['harga_tambahan'] !== '') {
                    $finishingGroups[$key]['master'] = [
                        'id_sku'               => $skuFinal,
                        'id_pilihan_finishing' => $rowData['id_pilihan_finishing'],
                        'minimum_pesan'        => $rowData['minimum_pesan'] === '' ? 1 : $rowData['minimum_pesan'],
                        'harga_tambahan'       => str_replace(['.', ','], ['', '.'], $rowData['harga_tambahan']),
                        'tipe'                 => empty($rowData['tipe']) ? 'nominal' : $rowData['tipe'],
                        'kali_jumlah_pesan'    => in_array(strtolower($rowData['kali_jumlah_pesan'] ?? ''), ['1', 'true', 'ya', 'y']) ? 1 : 0,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                }

                if (isset($rowData['min']) && $rowData['min'] !== '') {
                    $finishingGroups[$key]['tiers'][] = [
                        'min'        => $rowData['min'],
                        'max'        => (!isset($rowData['max']) || $rowData['max'] === '') ? 0 : $rowData['max'],
                        'tipe'       => empty($rowData['tipe_diskon']) ? 'nominal' : $rowData['tipe_diskon'],
                        'nilai'      => (!isset($rowData['nilai']) || $rowData['nilai'] === '') ? 0 : str_replace(['.', ','], ['', '.'], $rowData['nilai']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                continue;
            }

            // ==============================================================
            // LOGIC DISKON CUSTOMER & KOMPOSISI
            // ==============================================================
            if (in_array($request->tipe_import, ['diskon_customer', 'komposisi'])) {
                $dataRow = [];
                foreach ($headers as $headerName) {
                    $dataRow[$headerName] = $rowData[$headerName] ?? null;
                }

                $dataRow['created_at'] = $now;
                $dataRow['updated_at'] = $now;

                $insertData[] = $dataRow;
            }
        }

        // ==========================================================
        // KONVERSI MATRIKS MENJADI RECORD DATABASE
        // ==========================================================
        if ($request->tipe_import === 'harga_bertingkat') {
            foreach ($matrixHargaBertingkat as $sku => $slas) {

                // Siapkan urutan minQty untuk SKU ini kalau-kalau perlu di-auto hitung
                $uniqueMins = $allSkuMins[$sku] ?? [];
                sort($uniqueMins);

                foreach ($slas as $pengerjaan => $tiers) {
                    foreach ($tiers as $tier) {
                        $currentMin = $tier['min'];
                        $max = $tier['max'];

                        // Jika max bernilai -1 (alias user cuma ngetik "1 pack"), baru kita auto hitung
                        if ($max === -1) {
                            $pos = array_search($currentMin, $uniqueMins);
                            if ($pos !== false && isset($uniqueMins[$pos + 1])) {
                                $nextMin = $uniqueMins[$pos + 1];
                                $max = ($nextMin > $currentMin + 1) ? $nextMin - 1 : $currentMin;
                            } else {
                                $max = 0; // Seterusnya
                            }
                        }

                        $insertData[] = [
                            'id_sku'     => $sku,
                            'pengerjaan' => ucwords(strtolower($pengerjaan)),
                            'min'        => $currentMin,
                            'max'        => $max,
                            'tipe'       => 'nominal',
                            'nilai'      => $tier['nilai'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        if (empty($insertData) && empty($finishingGroups)) {
            return back()->withErrors(['message' => 'Tidak ada data valid yang sesuai dengan pilihan skala import.']);
        }

        try {
            DB::beginTransaction();

            if ($request->tipe_import === 'sku_finishing') {
                $existingFinishingIds = DB::table('sku_finishing')
                    ->whereIn('id_sku', $skusAffected)
                    ->pluck('id')
                    ->toArray();

                if (!empty($existingFinishingIds)) {
                    DB::table('sku_finishing_harga_bertingkat')
                        ->whereIn('sku_finishing_id', $existingFinishingIds)
                        ->delete();
                }

                DB::table('sku_finishing')->whereIn('id_sku', $skusAffected)->delete();

                foreach ($finishingGroups as $group) {
                    if ($group['master']) {
                        $newFinishingId = DB::table('sku_finishing')->insertGetId($group['master'], 'id');

                        if (!empty($group['tiers'])) {
                            $tiersToInsert = [];
                            foreach ($group['tiers'] as $tier) {
                                $tier['sku_finishing_id'] = $newFinishingId;
                                $tiersToInsert[] = $tier;
                            }
                            DB::table('sku_finishing_harga_bertingkat')->insert($tiersToInsert);
                        }
                    }
                }
            } else {
                DB::table($request->tipe_import)->whereIn('id_sku', $skusAffected)->delete();
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table($request->tipe_import)->insert($chunk);
                }
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
        $sku = ProdukSku::with([
            'produk',
            'hargaBertingkat',
            'skuFinishing.pilihanFinishing',
            'skuFinishing.hargaBertingkat'
        ])->findOrFail($id_sku);
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

    // ❌ METHOD hargaPengerjaan DIHAPUS ❌

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
