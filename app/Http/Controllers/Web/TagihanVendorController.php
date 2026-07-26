<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PesananItemProduksi;
use App\Models\TagihanVendor;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TagihanVendorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        // ==========================================
        // 1. QUERY PENDING (Menunggu Pembayaran)
        // ==========================================
        $pendingQuery = PesananItemProduksi::with(['vendor', 'pesananItem.pesan'])
            ->whereNotNull('id_vendor')
            ->whereNull('id_tagihan_vendor')
            ->orderBy('created_at', 'asc');

        if (!empty($search)) {
            $pendingQuery->where(function($q) use ($search) {
                $q->whereHas('vendor', function($qVendor) use ($search) {
                    $qVendor->where('nama_vendor', 'like', "%{$search}%")
                            ->orWhere('nama_bank', 'like', "%{$search}%")
                            ->orWhere('no_rekening', 'like', "%{$search}%")
                            ->orWhere('atas_nama', 'like', "%{$search}%");
                })
                ->orWhereHas('pesananItem.pesan', function($qPesan) use ($search) {
                    $qPesan->where('id_pesan', 'like', "%{$search}%");
                });
            });
        }

        $pendingRaw = $pendingQuery->get();

        $pendingTagihan = $pendingRaw->groupBy('id_vendor')->map(function ($items, $id_vendor) {
            $vendor = $items->first()->vendor;
            return [
                'id_vendor' => $id_vendor,
                'nama_vendor' => $vendor?->nama_vendor ?? 'Vendor Tidak Diketahui',
                'info_bank' => $vendor ? "{$vendor->nama_bank} - {$vendor->no_rekening} a.n {$vendor->atas_nama}" : '-',
                'jumlah_pekerjaan' => $items->count(),
                'total_hutang' => $items->sum('total_tagihan_vendor'),
                'items' => $items->map(function($i) {
                    return [
                        'id' => $i->id,
                        'tipe_pengerjaan' => $i->tipe_pengerjaan,
                        'qty_dikerjakan' => $i->qty_dikerjakan,
                        'total_tagihan_vendor' => $i->total_tagihan_vendor,
                        'id_pesan' => $i->pesananItem?->pesan?->id_pesan ?? '-',
                    ];
                })->values()
            ];
        })->values();

        // ==========================================
        // 2. QUERY RIWAYAT (Sudah Lunas)
        // ==========================================
        $riwayatQuery = TagihanVendor::with([
            'vendor',
            'pesananItemProduksi.pesananItem.pesan'
        ])
            ->whereNotNull('id_vendor')
            ->orderBy('tanggal_bayar', 'desc');

        if (!empty($search)) {
            $riwayatQuery->where(function($q) use ($search) {
                $q->where('kode_tagihan', 'like', "%{$search}%")
                  ->orWhere('nama_bank', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('atas_nama', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($qVendor) use ($search) {
                      $qVendor->where('nama_vendor', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pesananItemProduksi.pesananItem.pesan', function($qPesan) use ($search) {
                      $qPesan->where('id_pesan', 'like', "%{$search}%");
                  });
            });
        }

        // Tambahkan withQueryString agar parameter search tidak hilang pas pindah page
        $riwayatTagihan = $riwayatQuery->paginate(10)->withQueryString();

        return Inertia::render('TagihanVendor/Index', [
            'pendingTagihan' => $pendingTagihan,
            'riwayatTagihan' => $riwayatTagihan,
            'filters' => $request->only(['search']) // Kirim query ke Vue
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_vendor' => 'required|exists:vendor,id_vendor',
            'bukti_bayar.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,zip|max:204800',
        ]);

        DB::beginTransaction();
        try {
            $vendor = Vendor::findOrFail($request->id_vendor);

            $items = PesananItemProduksi::where('id_vendor', $request->id_vendor)
                ->whereNull('id_tagihan_vendor')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return redirect()->back()->withErrors(['error' => 'Tidak ada tagihan pending untuk vendor ini.']);
            }

            $buktiPath = null;
            if ($request->hasFile('bukti_bayar.file')) {
                $buktiPath = $request->file('bukti_bayar.file')->store('bukti_bayar_vendor', 'public');
            }

            $date = date('Y');
            $lastTagihan = TagihanVendor::where('kode_tagihan', 'like', "TGH-{$date}-%")->orderBy('kode_tagihan', 'desc')->first();
            $urutan = $lastTagihan ? intval(substr($lastTagihan->kode_tagihan, -4)) + 1 : 1;
            $kode_tagihan = "TGH-{$date}-" . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $tagihanBaru = TagihanVendor::create([
                'id_vendor' => $request->id_vendor,
                'kode_tagihan' => $kode_tagihan,
                'total_tagihan' => $items->sum('total_tagihan_vendor'),
                'nama_bank' => $vendor->nama_bank,
                'no_rekening' => $vendor->no_rekening,
                'atas_nama' => $vendor->atas_nama,
                'status' => 'lunas',
                'bukti_bayar' => $buktiPath,
                'tanggal_bayar' => now(),
            ]);

            PesananItemProduksi::whereIn('id', $items->pluck('id'))
                ->update(['id_tagihan_vendor' => $tagihanBaru->id]);

            $akunKas = BukuBesarController::getAkunId('Kas Bank (BCA/Mandiri/dll)');
            $akunHPP = BukuBesarController::getAkunId('Harga Pokok Produksi (HPP)');

            $nominalDibayar = $tagihanBaru->total_tagihan;

            if ($nominalDibayar > 0) {
                BukuBesarController::catatJurnal(
                    $akunHPP,
                    $tagihanBaru->kode_tagihan,
                    'pengeluaran',
                    "Pembayaran Jasa Produksi ke Vendor: {$vendor->nama_vendor}",
                    $nominalDibayar,
                    0
                );

                BukuBesarController::catatJurnal(
                    $akunKas,
                    $tagihanBaru->kode_tagihan,
                    'pengeluaran',
                    "Transfer Pembayaran Jasa Vendor: {$vendor->nama_vendor}",
                    0,
                    $nominalDibayar
                );
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pembayaran berhasil diproses dan bukti tersimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal memproses pembayaran: ' . $e->getMessage()]);
        }
    }
}
