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
            'harga' => 'required|numeric|min:0',
            'gambar' => 'nullable|array', // Validasi sebagai array
            'gambar.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048' // Validasi tiap isinya adalah file gambar
        ]);

        try {
            $sku = ProdukSku::findOrFail($id_sku);

            $dataUpdate = [
                'deskripsi' => $request->deskripsi,
                'tipe_kalkulasi' => $request->tipe_kalkulasi,
                'satuan' => $request->satuan,
                'minimum_pesan' => $request->minimum_pesan,
                'harga' => $request->harga,
            ];

            // 👇 Proses Upload Multiple Gambar
            if ($request->hasFile('gambar')) {
                // Hapus gambar-gambar lama jika ada
                if ($sku->gambar) {
                    $oldImages = is_array($sku->gambar) ? $sku->gambar : json_decode($sku->gambar, true) ?? [$sku->gambar];
                    foreach ($oldImages as $oldImg) {
                        Storage::disk('public')->delete($oldImg);
                    }
                }

                // Looping simpan gambar baru
                $paths = [];
                foreach ($request->file('gambar') as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $paths[] = $file->storeAs('produk_sku_images', $filename, 'public');
                }

                // Masukkan array path ke database
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

        // CONTAINER MATRIKS HARGA BERTINGKAT (SLA)
        $matrixHargaBertingkat = [];

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
            $skuKey = $headers[0]; // Kolom pertama (id_sku / varian)
            if (!empty($rowData[$skuKey])) {
                $currentSku = $rowData[$skuKey];
            } else {
                $rowData[$skuKey] = $currentSku;
            }

            if (empty($rowData[$skuKey])) {
                continue;
            }

            $skuFinal = $rowData[$skuKey];

            if ($request->skala_import === 'produk_ini' && !in_array($skuFinal, $validSkus)) {
                continue;
            }

            if (!in_array($skuFinal, $skusAffected)) {
                $skusAffected[] = $skuFinal;
            }

            // ==============================================================
            // 2. LOGIC KHUSUS HARGA BERTINGKAT & SLA (MATRIKS FORMAT)
            // ==============================================================
            if ($request->tipe_import === 'harga_bertingkat') {
                $jumlahKey = $headers[1]; // Kolom kedua (jumlah / pack)
                $jumlahRaw = $rowData[$jumlahKey] ?? '';

                if ($jumlahRaw === '') {
                    continue; // Lewati jika kolom jumlah kosong
                }

                // Ekstrak angka pertama dari text (Misal "1 pack" / "2 pack" -> 1, 2)
                preg_match('/\d+/', $jumlahRaw, $matches);
                $minQty = isset($matches[0]) ? (int)$matches[0] : 0;

                if ($minQty <= 0) {
                    continue;
                }

                // Loop dinamis mulai dari kolom ke-3 (index 2) untuk membaca Header SLA (5 hari, 3 hari, dll)
                foreach ($headers as $index => $headerName) {
                    if ($index < 2) continue; // Skip kolom id_sku dan jumlah

                    $nilaiRaw = $rowData[$headerName] ?? '';
                    if ($nilaiRaw !== '' && $nilaiRaw !== '-') {
                        // Bersihkan titik ribuan format Excel (misal 100.000 -> 100000)
                        $nilaiBersih = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d.,]/', '', $nilaiRaw));

                        if ($nilaiBersih > 0) {
                            $matrixHargaBertingkat[$skuFinal][$headerName][] = [
                                'min'   => $minQty,
                                'nilai' => $nilaiBersih
                            ];
                        }
                    }
                }
                continue;
            }

            // ==============================================================
            // 3. LOGIC FINISHING & TABEL LAINNYA
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

            if ($request->tipe_import === 'komposisi') {
                $insertData[] = [
                    'id_sku'               => $skuFinal,
                    'id_bahan_baku'        => $rowData['id_bahan_baku'] ?? null,
                    'id_pilihan_finishing' => empty($rowData['id_pilihan_finishing']) ? null : $rowData['id_pilihan_finishing'],
                    'jumlah_pakai'         => $rowData['jumlah_pakai'] ?? 0,
                    'hpp'                  => $rowData['hpp'] ?? 0,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }
            if ($request->tipe_import === 'diskon_customer') {
                $insertData[] = [
                    'id_sku'           => $skuFinal,
                    'id_role_customer' => $rowData['id_role_customer'] ?? '',
                    'tipe'             => $rowData['tipe'] ?? 'nominal',
                    'nilai'            => $rowData['nilai'] ?? 0,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        // ==========================================================
        // KONVERSI MATRIKS MENJADI RECORD DATABASE (MIN & MAX OTOMATIS)
        // ==========================================================
        if ($request->tipe_import === 'harga_bertingkat') {
            foreach ($matrixHargaBertingkat as $sku => $slas) {
                foreach ($slas as $pengerjaan => $tiers) {

                    // Urutkan tier berdasarkan min qty dari kecil ke besar
                    usort($tiers, function($a, $b) {
                        return $a['min'] <=> $b['min'];
                    });

                    $count = count($tiers);
                    for ($i = 0; $i < $count; $i++) {
                        $currentMin = $tiers[$i]['min'];

                        // Tentukan max: jika ada tier setelahnya, max = min_berikutnya - 1.
                        // Jika ini adalah tier terakhir, max = 0 (artinya tak terhingga / dan seterusnya).
                        $max = 0;
                        if ($i < $count - 1) {
                            $nextMin = $tiers[$i+1]['min'];
                            // Jika jarak min-nya berurutan (misal 1, 2, 3), maka max sama dengan min (misal min:1, max:1).
                            // Jika jaraknya melompat (misal 1, 5), maka max mengikuti rentang (misal min:1, max:4).
                            if ($nextMin > $currentMin + 1) {
                                $max = $nextMin - 1;
                            } else {
                                $max = $currentMin;
                            }
                        } else {
                            // Untuk baris terakhir, jika jarak loncatannya 1 dari sebelumnya, buat max = currentMin.
                            // Atau jika baris terakhir adalah baris tunggal penutup, jadikan max = 0 (tak terhingga).
                            if ($count > 1 && $currentMin == $tiers[$i-1]['min'] + 1) {
                                $max = $currentMin;
                            } else {
                                $max = 0; // Tak terhingga untuk baris terakhir
                            }
                        }

                        // Khusus jika di spreadsheet barisnya satuan (1, 2, 3, 4, 5)
                        if ($count >= 5 && $currentMin <= 5) {
                            $max = ($i < $count - 1) ? $currentMin : 0;
                        }

                        $insertData[] = [
                            'id_sku'     => $sku,
                            'pengerjaan' => ucwords(strtolower($pengerjaan)),
                            'min'        => $currentMin,
                            'max'        => $max,
                            'tipe'       => 'nominal',
                            'nilai'      => $tiers[$i]['nilai'],
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
                // Hapus data lama hanya pada SKU yang terdampak import
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
