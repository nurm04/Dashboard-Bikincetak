<?php

namespace App\Http\Controllers\Web\Produk;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Finishing;
use App\Models\PilihanFinishing;
use App\Models\ProdukSku;
use App\Models\RoleCustomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProdukSkuController extends Controller
{
    public function syncSku(Request $request, $id_produk)
    {
        $request->validate([
            'skus' => 'nullable|array',
        ]);

        // Longgarkan batas waktu eksekusi & memori khusus untuk generate SKU raksasa
        ini_set('max_execution_time', 300); // 5 menit
        ini_set('memory_limit', '512M');

        try {
            DB::beginTransaction();

            $incomingSkus = collect($request->skus ?? []);
            $incomingIds = $incomingSkus->pluck('id_sku')->filter()->toArray();

            // 1. Hapus SKU yang tidak ada di daftar form (Dihapus dari UI)
            ProdukSku::where('id_produk', $id_produk)
                ->whereNotIn('id_sku', $incomingIds)
                ->delete();

            // 2. Cari index terakhir untuk Auto-Increment custom ID SKU
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

            // CONTAINER UNTUK BULK INSERT
            $newSkusData = [];
            $newPivotData = [];
            $now = Carbon::now();

            if (!empty($request->skus)) {
                foreach ($request->skus as $data) {
                    if (!empty($data['id_sku'])) {
                        // JIKA SKU LAMA (UPDATE) -> Gunakan metode Query Builder agar super cepat
                        DB::table('produk_sku')->where('id_sku', $data['id_sku'])->update([
                            'nama_sku' => $data['nama_sku'],
                            'minimum_pesan' => $data['minimum_pesan'],
                            'harga' => $data['harga'],
                            'updated_at' => $now
                        ]);

                        // Hapus pivot pilihan lama, siapkan pivot baru
                        DB::table('sku_detail_pilihan')->where('id_sku', $data['id_sku'])->delete();
                        foreach ($data['pilihan_ids'] as $id_pilihan) {
                            $newPivotData[] = [
                                'id_sku' => $data['id_sku'],
                                'id_pilihan' => $id_pilihan,
                                'created_at' => $now,
                                'updated_at' => $now
                            ];
                        }
                    } else {
                        // JIKA SKU BARU (INSERT) -> Kumpulkan di array
                        $lastIndex++;
                        $skuId = $id_produk . "-SKU-" . str_pad($lastIndex, 3, '0', STR_PAD_LEFT);

                        $newSkusData[] = [
                            'id_sku' => $skuId,
                            'id_produk' => $id_produk,
                            'nama_sku' => $data['nama_sku'],
                            'minimum_pesan' => $data['minimum_pesan'],
                            'harga' => $data['harga'],
                            'tipe_kalkulasi' => 'standard', // default mandatory value
                            'created_at' => $now,
                            'updated_at' => $now
                        ];

                        foreach ($data['pilihan_ids'] as $id_pilihan) {
                            $newPivotData[] = [
                                'id_sku' => $skuId,
                                'id_pilihan' => $id_pilihan,
                                'created_at' => $now,
                                'updated_at' => $now
                            ];
                        }
                    }
                }
            }

            // 3. EKSEKUSI BULK INSERT (Dibagi per 500 baris agar server tidak ngos-ngosan)
            if (!empty($newSkusData)) {
                foreach (array_chunk($newSkusData, 500) as $chunk) {
                    DB::table('produk_sku')->insert($chunk);
                }
            }

            if (!empty($newPivotData)) {
                foreach (array_chunk($newPivotData, 500) as $chunk) {
                    DB::table('sku_detail_pilihan')->insert($chunk);
                }
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'SKU Produk berhasil diperbarui dengan cepat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
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
            'tipe_import'  => 'required|in:data_utama_sku,sku_finishing,harga_bertingkat,diskon_customer,komposisi',
            'file_csv'     => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file_csv');
        $csvData = array_map('str_getcsv', file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        if (count($csvData) < 2) {
            return back()->withErrors(['message' => 'File CSV kosong atau tidak memiliki baris data.']);
        }

        $headers = array_map('trim', $csvData[0]);
        $headers[0] = preg_replace('/[\xef\xbb\xbf]/', '', $headers[0]); // Hapus BOM

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
        $matrixHargaBertingkat = [];
        $allSkuMins = [];

        try {
            DB::beginTransaction();

            // ==============================================================================
            // 🌟 EKSEKUSI KHUSUS UNTUK DATA UTAMA SKU (UPDATE) 🌟
            // ==============================================================================
            if ($request->tipe_import === 'data_utama_sku') {
                foreach ($rows as $row) {
                    if (count($row) < count($headers)) $row = array_pad($row, count($headers), '');
                    if (empty(array_filter($row))) continue;

                    $row = array_map('trim', $row);
                    $rowData = array_combine($headers, $row);

                    $skuKey = $headers[0];
                    if (!empty($rowData[$skuKey])) {
                        $currentSku = $rowData[$skuKey];
                    } else {
                        $rowData[$skuKey] = $currentSku;
                    }

                    $skuFinal = $rowData[$skuKey] ?? '';
                    if (empty($skuFinal)) continue;
                    if ($request->skala_import === 'produk_ini' && !in_array($skuFinal, $validSkus)) continue;

                    $updateData = [];
                    if (array_key_exists('deskripsi', $rowData)) $updateData['deskripsi'] = $rowData['deskripsi'];
                    if (!empty($rowData['tipe_kalkulasi'])) $updateData['tipe_kalkulasi'] = strtolower($rowData['tipe_kalkulasi']);
                    if (array_key_exists('satuan', $rowData)) $updateData['satuan'] = $rowData['satuan'];
                    if (isset($rowData['minimum_pesan']) && is_numeric($rowData['minimum_pesan'])) $updateData['minimum_pesan'] = $rowData['minimum_pesan'];
                    if (isset($rowData['kelipatan_pesan']) && is_numeric($rowData['kelipatan_pesan'])) $updateData['kelipatan_pesan'] = $rowData['kelipatan_pesan'];

                    // 👇 PEMBULATAN KELIPATAN 50 KE ATAS (DATA UTAMA) 👇
                    if (isset($rowData['harga']) && is_numeric($rowData['harga'])) {
                        $rawHarga = (float) str_replace(['.', ','], ['', '.'], $rowData['harga']);
                        $updateData['harga'] = ceil($rawHarga / 50) * 50;
                    }
                    if (isset($rowData['harga_tambahan_dimensi']) && is_numeric($rowData['harga_tambahan_dimensi'])) {
                        $rawDimensi = (float) str_replace(['.', ','], ['', '.'], $rowData['harga_tambahan_dimensi']);
                        $updateData['harga_tambahan_dimensi'] = ceil($rawDimensi / 50) * 50;
                    }

                    if (!empty($updateData)) {
                        $updateData['updated_at'] = $now;
                        DB::table('produk_sku')->where('id_sku', $skuFinal)->update($updateData);
                    }
                }

                DB::commit();
                return back()->with('success', 'Data Utama SKU berhasil di-update massal!');
            }


            // ==============================================================================
            // 🌟 LANJUTAN LOGIC CSV LAMA 🌟
            // ==============================================================================
            foreach ($rows as $row) {
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }
                if (empty(array_filter($row))) continue;

                $row = array_map('trim', $row);
                $rowData = array_combine($headers, $row);

                $skuKey = $headers[0];
                if (!empty($rowData[$skuKey])) {
                    $currentSku = $rowData[$skuKey];
                } else {
                    $rowData[$skuKey] = $currentSku;
                }

                if (empty($rowData[$skuKey])) continue;
                $skuFinal = $rowData[$skuKey];

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

                // HARGA BERTINGKAT (MATRIKS)
                if ($request->tipe_import === 'harga_bertingkat') {
                    $jumlahKey = $headers[1];
                    $jumlahRaw = strtolower(trim($rowData[$jumlahKey] ?? ''));

                    if ($jumlahRaw === '') continue;

                    preg_match_all('/\d+/', str_replace(['.', ','], '', $jumlahRaw), $matches);
                    $minQty = isset($matches[0][0]) ? (int)$matches[0][0] : 0;

                    if ($minQty <= 0) continue;

                    $maxQty = isset($matches[0][1]) ? (int)$matches[0][1] : -1;

                    if (!isset($allSkuMins[$skuFinal])) $allSkuMins[$skuFinal] = [];
                    if (!in_array($minQty, $allSkuMins[$skuFinal])) {
                        $allSkuMins[$skuFinal][] = $minQty;
                    }

                    foreach ($headers as $index => $headerName) {
                        if ($index < 2) continue;

                        $nilaiRaw = $rowData[$headerName] ?? '';
                        if ($nilaiRaw !== '' && $nilaiRaw !== '-') {
                            $nilaiBersih = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d.,]/', '', $nilaiRaw));

                            // 👇 PEMBULATAN KELIPATAN 50 KE ATAS (MATRIKS CSV) 👇
                            if ($nilaiBersih > 0) {
                                $nilaiBersih = ceil($nilaiBersih / 50) * 50;

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

                // FINISHING TABEL
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
                        $tipe = empty($rowData['tipe']) ? 'nominal' : strtolower($rowData['tipe']);
                        $rawHarga = (float) str_replace(['.', ','], ['', '.'], $rowData['harga_tambahan']);

                        // 👇 PEMBULATAN 50 (HANYA JIKA TIPE NOMINAL, BUKAN PERSEN) 👇
                        $hargaFinal = ($tipe === 'nominal' && $rawHarga > 0) ? ceil($rawHarga / 50) * 50 : $rawHarga;

                        $finishingGroups[$key]['master'] = [
                            'id_sku'               => $skuFinal,
                            'id_pilihan_finishing' => $rowData['id_pilihan_finishing'],
                            'minimum_pesan'        => $rowData['minimum_pesan'] === '' ? 1 : $rowData['minimum_pesan'],
                            'harga_tambahan'       => $hargaFinal,
                            'tipe'                 => $tipe,
                            'kali_jumlah_pesan'    => in_array(strtolower($rowData['kali_jumlah_pesan'] ?? ''), ['1', 'true', 'ya', 'y']) ? 1 : 0,
                            'created_at'           => $now,
                            'updated_at'           => $now,
                        ];
                    }

                    if (isset($rowData['min']) && $rowData['min'] !== '') {
                        $tipeDiskon = empty($rowData['tipe_diskon']) ? 'nominal' : strtolower($rowData['tipe_diskon']);
                        $rawNilai = (!isset($rowData['nilai']) || $rowData['nilai'] === '') ? 0 : (float) str_replace(['.', ','], ['', '.'], $rowData['nilai']);

                        // 👇 PEMBULATAN 50 UNTUK TIER FINISHING 👇
                        $nilaiFinal = ($tipeDiskon === 'nominal' && $rawNilai > 0) ? ceil($rawNilai / 50) * 50 : $rawNilai;

                        $finishingGroups[$key]['tiers'][] = [
                            'min'        => $rowData['min'],
                            'max'        => (!isset($rowData['max']) || $rowData['max'] === '') ? 0 : $rowData['max'],
                            'tipe'       => $tipeDiskon,
                            'nilai'      => $nilaiFinal,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    continue;
                }

                // DISKON CUSTOMER & KOMPOSISI
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

            $skuLowestPrices = [];

            // KONVERSI MATRIKS (HARGA BERTINGKAT)
            if ($request->tipe_import === 'harga_bertingkat') {
                foreach ($matrixHargaBertingkat as $sku => $slas) {
                    $uniqueMins = $allSkuMins[$sku] ?? [];
                    sort($uniqueMins);

                    foreach ($slas as $pengerjaan => $tiers) {
                        foreach ($tiers as $tier) {
                            $currentMin = $tier['min'];
                            $max = $tier['max'];
                            $currentPrice = $tier['nilai'];

                            if (!isset($skuLowestPrices[$sku])) {
                                $skuLowestPrices[$sku] = ['min' => $currentMin, 'harga' => $currentPrice];
                            } else {
                                if ($currentMin < $skuLowestPrices[$sku]['min']) {
                                    $skuLowestPrices[$sku] = ['min' => $currentMin, 'harga' => $currentPrice];
                                } elseif ($currentMin == $skuLowestPrices[$sku]['min'] && $currentPrice < $skuLowestPrices[$sku]['harga']) {
                                    $skuLowestPrices[$sku]['harga'] = $currentPrice;
                                }
                            }

                            if ($max === -1) {
                                $pos = array_search($currentMin, $uniqueMins);
                                if ($pos !== false && isset($uniqueMins[$pos + 1])) {
                                    $nextMin = $uniqueMins[$pos + 1];
                                    $max = ($nextMin > $currentMin + 1) ? $nextMin - 1 : $currentMin;
                                } else {
                                    $max = 0;
                                }
                            }

                            $insertData[] = [
                                'id_sku'     => $sku,
                                'pengerjaan' => ucwords(strtolower($pengerjaan)),
                                'min'        => $currentMin,
                                'max'        => $max,
                                'tipe'       => 'nominal',
                                'nilai'      => $currentPrice,
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

                // UPDATE HARGA DASAR DI TABEL PRODUK_SKU SECARA MASSAL
                if ($request->tipe_import === 'harga_bertingkat' && !empty($skuLowestPrices)) {
                    foreach ($skuLowestPrices as $skuId => $data) {
                        DB::table('produk_sku')->where('id_sku', $skuId)->update([
                            'harga' => $data['harga'],
                            'updated_at' => $now
                        ]);
                    }
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


    public function syncSpreadsheet(Request $request, $id_produk)
    {
        $request->validate([
            'skala_import' => 'required|in:produk_ini',
            'tipe_import'  => 'required|in:harga_bertingkat',
            'sheet_url'    => 'required|url',
            'filter_qty'   => 'nullable|string',
        ]);

        $url = $request->sheet_url;

        $allowedQtys = [];
        if (!empty($request->filter_qty)) {
            $allowedQtys = array_map('intval', array_map('trim', explode(',', $request->filter_qty)));
        }

        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $idMatch);
        preg_match('/gid=([0-9]+)/', $url, $gidMatch);

        if (empty($idMatch)) {
            return back()->withErrors(['sheet_url' => 'Link Google Sheets tidak valid.']);
        }

        $spreadsheetId = $idMatch[1];
        $gid = !empty($gidMatch) ? $gidMatch[1] : '0';
        $csvExportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";

        try {
            $response = Http::get($csvExportUrl);
            if (!$response->successful()) {
                return back()->withErrors(['sheet_url' => 'Gagal menarik data. Pastikan akses "Anyone with the link can view".']);
            }

            $csvData = $response->body();
            $rows = array_map('str_getcsv', explode("\n", trim($csvData)));

            $skus = DB::table('produk_sku')->where('id_produk', $id_produk)->get();

            $findSku = function($skus, $ukuran, $bahan, $lembar) {
                $ukClean = preg_replace('/[^0-9x]/', '', strtolower($ukuran));
                $bhClean = str_replace('gr', '', strtolower($bahan));
                $bhParts = array_filter(explode(' ', trim($bhClean)));

                foreach ($skus as $sku) {
                    $namaLengkap = strtolower($sku->nama_sku);
                    $namaNoSpace = str_replace(' ', '', $namaLengkap);

                    $matchBahan = true;
                    foreach ($bhParts as $part) {
                        if (strpos($namaLengkap, $part) === false) {
                            $matchBahan = false; break;
                        }
                    }

                    $matchUkuran = strpos($namaNoSpace, $ukClean) !== false;
                    $matchLembar = preg_match("/\b{$lembar}\s*(lembar|halaman)\b/i", $namaLengkap) === 1;

                    if ($matchBahan && $matchUkuran && $matchLembar) {
                        return $sku->id_sku;
                    }
                }
                return null;
            };

            $matrixHargaBertingkat = [];
            $allSkuMins = [];

            for ($r = 0; $r < count($rows); $r++) {
                for ($c = 0; $c < count($rows[$r]); $c++) {
                    $cellValue = strtolower(trim($rows[$r][$c] ?? ''));

                    if ($cellValue === 'jumlah') {

                        $activeUkuran = '';
                        $activeBahan = '';
                        for ($up = $r - 1; $up >= 0; $up--) {
                            if (!empty(trim($rows[$up][$c] ?? ''))) {
                                $activeUkuran = trim($rows[$up][$c]);
                                $activeBahan = trim($rows[$up][$c + 1] ?? '');
                                break;
                            }
                        }

                        if (empty($activeUkuran) || empty($activeBahan)) continue;

                        $activeLembarList = [];
                        for ($right = $c + 1; $right < count($rows[$r]); $right++) {
                            $lembarVal = trim($rows[$r][$right] ?? '');
                            if ($lembarVal !== '') {
                                $activeLembarList[$right] = $lembarVal;
                            } else {
                                break;
                            }
                        }

                        for ($down = $r + 1; $down < count($rows); $down++) {
                            $minQtyRaw = trim($rows[$down][$c] ?? '');

                            if ($minQtyRaw === '') continue;

                            $cleanQty = str_replace(['.', ','], '', $minQtyRaw);
                            if (!is_numeric($cleanQty)) break;

                            $minQty = (int) $cleanQty;

                            if (!empty($allowedQtys) && !in_array($minQty, $allowedQtys)) {
                                continue;
                            }

                            foreach ($activeLembarList as $colIdx => $lembar) {
                                $hargaRaw = $rows[$down][$colIdx] ?? '';
                                if (trim($hargaRaw) !== '') {
                                    $harga = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d.,]/', '', $hargaRaw));

                                    // 👇 PEMBULATAN KELIPATAN 50 KE ATAS (SYNC SPREADSHEET) 👇
                                    if ($harga > 0) {
                                        $harga = ceil($harga / 50) * 50;

                                        $matchedSkuId = $findSku($skus, $activeUkuran, $activeBahan, $lembar);

                                        if ($matchedSkuId) {
                                            if (!isset($allSkuMins[$matchedSkuId])) $allSkuMins[$matchedSkuId] = [];
                                            if (!in_array($minQty, $allSkuMins[$matchedSkuId])) {
                                                $allSkuMins[$matchedSkuId][] = $minQty;
                                            }

                                            $sla = "3 Hari";
                                            if ($minQty < 300) $sla = "3 Hari";
                                            elseif ($minQty >= 300 && $minQty <= 500) $sla = "3 Hari";
                                            elseif ($minQty >= 501 && $minQty <= 1000) $sla = "5 Hari";
                                            elseif ($minQty >= 1001 && $minQty <= 2000) $sla = "7 Hari";
                                            elseif ($minQty >= 2001 && $minQty <= 3000) $sla = "9 Hari";
                                            elseif ($minQty >= 3001) $sla = "11 Hari";

                                            $matrixHargaBertingkat[$matchedSkuId][] = [
                                                'min' => $minQty,
                                                'nilai' => $harga,
                                                'pengerjaan' => $sla
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (empty($matrixHargaBertingkat)) {
                return back()->withErrors(['sheet_url' => 'Tidak ada data yang berhasil disinkronkan. Pastikan filter Qty benar dan nama SKU di database mengandung kombinasi bahan/ukuran tersebut.']);
            }

            $insertData = [];
            $now = Carbon::now();
            $skusAffected = array_keys($matrixHargaBertingkat);

            $skuLowestPrices = [];

            foreach ($matrixHargaBertingkat as $skuId => $tiers) {
                $uniqueMins = $allSkuMins[$skuId] ?? [];
                sort($uniqueMins);

                foreach ($tiers as $tier) {
                    $currentMin = $tier['min'];
                    $currentPrice = $tier['nilai'];
                    $max = 0;

                    if (!isset($skuLowestPrices[$skuId])) {
                        $skuLowestPrices[$skuId] = ['min' => $currentMin, 'harga' => $currentPrice];
                    } else {
                        if ($currentMin < $skuLowestPrices[$skuId]['min']) {
                            $skuLowestPrices[$skuId] = ['min' => $currentMin, 'harga' => $currentPrice];
                        } elseif ($currentMin == $skuLowestPrices[$skuId]['min'] && $currentPrice < $skuLowestPrices[$skuId]['harga']) {
                            $skuLowestPrices[$skuId]['harga'] = $currentPrice;
                        }
                    }

                    $pos = array_search($currentMin, $uniqueMins);
                    if ($pos !== false && isset($uniqueMins[$pos + 1])) {
                        $nextMin = $uniqueMins[$pos + 1];
                        $max = ($nextMin > $currentMin + 1) ? $nextMin - 1 : $currentMin;
                    }

                    $insertData[] = [
                        'id_sku'     => $skuId,
                        'pengerjaan' => $tier['pengerjaan'],
                        'min'        => $currentMin,
                        'max'        => $max,
                        'tipe'       => 'nominal',
                        'nilai'      => $currentPrice,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::beginTransaction();
            DB::table('harga_bertingkat')->whereIn('id_sku', $skusAffected)->delete();

            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table('harga_bertingkat')->insert($chunk);
            }

            // UPDATE HARGA DASAR DI TABEL PRODUK_SKU SECARA MASSAL
            if (!empty($skuLowestPrices)) {
                foreach ($skuLowestPrices as $skuId => $data) {
                    DB::table('produk_sku')->where('id_sku', $skuId)->update([
                        'harga' => $data['harga'],
                        'updated_at' => $now
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Data harga berhasil disinkronkan dan dibulatkan dari Spreadsheet!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['sheet_url' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
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
