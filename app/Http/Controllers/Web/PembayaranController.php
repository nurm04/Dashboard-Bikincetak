<?php

namespace App\Http\Controllers\Web;

use App\Events\ProduksiBaruEvent;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesan;
use App\Services\PembayaranService;
use App\Services\PesanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Pembayaran::with(['pesan.customer.user', 'staf.user', 'pesan.pesananItem.pesananItemFinishing']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {

                $q->where('id_pembayaran', 'like', "%{$search}%")
                  ->orWhere('nominal_bayar', 'like', "%{$search}%")
                  ->orWhere('metode_pembayaran', 'like', "%{$search}%")
                  ->orWhere('status_pembayaran', 'like', "%{$search}%")
                  ->orWhere('created_at', 'like', "%{$search}%")

                  ->orWhereHas('pesan', function($qPesan) use ($search) {
                      $qPesan->where('id_pesan', 'like', "%{$search}%")

                             ->orWhereHas('customer', function($qCust) use ($search) {
                                 $qCust->where('id_customer', 'like', "%{$search}%")
                                       ->orWhere('no_hp', 'like', "%{$search}%")
                                       ->orWhereHas('user', function($qUser) use ($search) {
                                           $qUser->where('name', 'like', "%{$search}%")
                                                 ->orWhere('email', 'like', "%{$search}%");
                                       });
                             });
                  })

                  ->orWhereHas('staf', function($qStaf) use ($search) {
                      $qStaf->where('id_staf', 'like', "%{$search}%")
                            ->orWhere('no_hp', 'like', "%{$search}%")
                            ->orWhereHas('user', function($qUser) use ($search) {
                                $qUser->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%");
                            });
                  });
            });
        }

        $pembayaran = $query->latest()
            ->get()
            ->map(function ($item) {
                $totalTagihan = $item->pesan ? PesanService::hitungTotalPesanan($item->pesan) : 0;
                $kodeUnik = $item->pesan ? PesanService::generateKodeUnik($item->pesan->id_pesan) : 0;

                $item->total_tagihan = $totalTagihan;
                $item->total_transfer = $totalTagihan + $kodeUnik;

                return $item;
            });

        return inertia('Pembayaran/Index', [
            'pembayaran' => $pembayaran,
            'filters' => $request->only(['search'])
        ]);
    }

    public function detail($id)
    {
        $pembayaran = Pembayaran::with([
            'pesan.customer.user',
            'staf.user',
            'pesan.pesanan_item.pesanan_item_finishing'
        ])
            ->where('id_pembayaran', $id)
            ->firstOrFail();

        $totalTagihan = $pembayaran->pesan ? PesanService::hitungTotalPesanan($pembayaran->pesan) : 0;
        $kodeUnik = $pembayaran->pesan ? PesanService::generateKodeUnik($pembayaran->pesan->id_pesan) : 0;

        $pembayaran->total_tagihan = $totalTagihan;
        $pembayaran->total_transfer = $totalTagihan + $kodeUnik;
        $pembayaran->kode_unik = $kodeUnik;

        return Inertia::render('Pembayaran/Detail', [
            'pembayaran' => $pembayaran
        ]);
    }

    public function store(Request $request, $id_pesan)
    {
        $staf = auth()->user()?->staf;

        $request->validate([
            'status_pembayaran' => 'required|in:belum_lunas,dibayar_sebagian,lunas',
            'nominal_bayar' => 'nullable|numeric|min:1'
        ]);

        $pesan = Pesan::with(['pesananItem.pesananItemFinishing', 'pembayaran'])->findOrFail($id_pesan);

        $rincian = PesanService::kalkulasiRincianPesanan($pesan);
        $totalTagihan = $rincian['grand_total'];
        $sisaTagihan = $rincian['sisa_tagihan'];

        DB::beginTransaction();

        try {
            $nominalDibayar = 0;
            $idPembayaran = null;

            if ($request->status_pembayaran === 'lunas') {
                $nominalDibayar = $sisaTagihan;

                if ($nominalDibayar > 0) {
                    $idPembayaran = PembayaranService::generateId();

                    Pembayaran::create([
                        'id_pembayaran'     => $idPembayaran,
                        'id_pesan'          => $pesan->id_pesan,
                        'nominal_bayar'     => $nominalDibayar,
                        'metode_pembayaran' => 'transfer_manual',
                        'status_pembayaran' => 'berhasil',
                        'id_staf'           => $staf?->id_staf,
                    ]);
                }

                $pesan->status_pembayaran = 'lunas';
            }

            if ($request->status_pembayaran === 'dibayar_sebagian') {
                $nominalDibayar = $request->nominal_bayar;

                if ($nominalDibayar > $sisaTagihan) {
                    $nominalDibayar = $sisaTagihan;
                }

                if ($nominalDibayar > 0) {
                    $idPembayaran = PembayaranService::generateId();

                    Pembayaran::create([
                        'id_pembayaran'     => $idPembayaran,
                        'id_pesan'          => $pesan->id_pesan,
                        'nominal_bayar'     => $nominalDibayar,
                        'metode_pembayaran' => 'transfer_manual',
                        'status_pembayaran' => 'berhasil',
                        'id_staf'           => $staf?->id_staf,
                    ]);
                }

                $totalDibayarBaru = Pembayaran::where('id_pesan', $pesan->id_pesan)->sum('nominal_bayar');
                $pesan->status_pembayaran = $totalDibayarBaru >= $totalTagihan ? 'lunas' : 'dibayar_sebagian';
            }

            if ($request->status_pembayaran === 'belum_lunas') {
                $pesan->status_pembayaran = 'belum_lunas';
            }

            if (in_array($pesan->status_pembayaran, ['dibayar_sebagian', 'lunas']) && is_null($pesan->waktu_deadline)) {
                $maxHari = 1;

                foreach ($pesan->pesananItem as $item) {
                    if (preg_match('/(\d+)/', $item->estimasi_pengerjaan_snapshot, $matches)) {
                        $hari = (int) $matches[1];
                        if ($hari > $maxHari) {
                            $maxHari = $hari;
                        }
                    }
                }

                $pesan->waktu_deadline = Carbon::now()->addDays($maxHari);
            }

            $pesan->save();

            if ($nominalDibayar > 0 && $idPembayaran) {
                $akunKas        = BukuBesarController::getAkunId('Kas Bank (BCA/Mandiri/dll)');
                $akunPendapatan = BukuBesarController::getAkunId('Pendapatan Jasa Percetakan');

                BukuBesarController::catatJurnal(
                    $akunKas,
                    $idPembayaran,
                    'pendapatan',
                    "Penerimaan Pembayaran Pesanan #{$pesan->id_pesan}",
                    $nominalDibayar,
                    0
                );

                BukuBesarController::catatJurnal(
                    $akunPendapatan,
                    $idPembayaran,
                    'pendapatan',
                    "Pendapatan Penjualan Pesanan #{$pesan->id_pesan}",
                    0,
                    $nominalDibayar
                );
            }

            event(new ProduksiBaruEvent($pesan));

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil dicatat & masuk ke Buku Besar.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

    }
}
