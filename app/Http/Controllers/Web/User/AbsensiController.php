<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Staf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // 👇 GANTI DENGAN KOORDINAT KANTOR/RUMAH LU (Ambil dari Google Maps)
    private $officeLat = -7.127083;
    private $officeLong = 112.721294;
    // private $officeLat = -7.304226;
    // private $officeLong = 112.756378;
    private $maxRadius = 100; // Jarak maksimal dalam meter

    public function index()
    {
        $user = Auth::user();
        $staf = Staf::where('user_id', $user->id)->firstOrFail();

        $sekarang = Carbon::now();
        $hariIni = $sekarang->toDateString();
        $tanggalPencarian = $hariIni;

        // Cek shift malam dari kemarin yang belum di-checkout (maksimal sebelum jam 07:00 pagi)
        if ($sekarang->hour < 7) {
            $kemarin = $sekarang->copy()->subDay()->toDateString();
            $cekGantung = Absensi::where('id_staf', $staf->id_staf)
                ->where('tanggal', $kemarin)
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_keluar')
                ->exists();

            if ($cekGantung) $tanggalPencarian = $kemarin;
        }

        // Ambil data secara terpisah berdasarkan Tipe Absen
        $absensiReguler = Absensi::where('id_staf', $staf->id_staf)->where('tanggal', $tanggalPencarian)->where('tipe_absen', 'Reguler')->first();
        $absensiLembur = Absensi::where('id_staf', $staf->id_staf)->where('tanggal', $tanggalPencarian)->where('tipe_absen', 'Lembur')->first();

        return Inertia::render('Staf/Absensi/Index', [
            'absensi_reguler' => $absensiReguler,
            'absensi_lembur' => $absensiLembur,
            'office_lat' => $this->officeLat,
            'office_long' => $this->officeLong,
            'max_radius' => $this->maxRadius,
        ]);
    }

    public function rekap(Request $request)
    {
        $user = Auth::user();
        $staf = Staf::with('roleStaf')->where('user_id', $user->id)->firstOrFail();

        if ($staf->id_role_staf !== 'ROLE-STAF-ADMIN' && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $search = $request->input('search');
        $bulan = $request->input('bulan', Carbon::now()->format('m'));
        $tahun = $request->input('tahun', Carbon::now()->format('Y'));

        $absensis = Absensi::with(['staf.user', 'staf.roleStaf'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($search, function ($query, $search) {
                $query->whereHas('staf.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            // Urutkan Tanggal dulu, lalu Reguler di atas Lembur, lalu berdasarkan ID Staf
            ->orderBy('tanggal', 'desc')
            ->orderBy('tipe_absen', 'desc')
            ->orderBy('id_staf', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Staf/Absensi/Rekap', [
            'absensis' => $absensis,
            'filters' => [
                'search' => $search,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe' => 'required|in:masuk,keluar,masuk_lembur,keluar_lembur',
            'foto' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        $user = Auth::user();
        $staf = Staf::where('user_id', $user->id)->firstOrFail();
        $sekarang = Carbon::now();

        $jarak = $this->hitungJarakMeter($this->officeLat, $this->officeLong, $request->lat, $request->long);
        if ($jarak > $this->maxRadius) {
            return redirect()->back()->with('error', "Anda berada di luar area kantor! Jarak Anda: " . round($jarak) . " meter.");
        }

        $isMinggu = $sekarang->isSunday();
        $tipeAbsen = in_array($request->tipe, ['masuk_lembur', 'keluar_lembur']) ? 'Lembur' : 'Reguler';

        // ATURAN 1: Absen di hari Minggu otomatis dianggap Lembur
        if ($isMinggu) {
            $tipeAbsen = 'Lembur';
            if ($request->tipe === 'masuk') $request->merge(['tipe' => 'masuk_lembur']);
            if ($request->tipe === 'keluar') $request->merge(['tipe' => 'keluar_lembur']);
        }

        // Auto-correct: Jika klik 'Absen Masuk' di luar jam kerja (bukan hari Minggu), alihkan jadi 'masuk_lembur'
        if (!$isMinggu && $request->tipe === 'masuk' && ($sekarang->hour >= 17 || $sekarang->hour < 7)) {
            $tipeAbsen = 'Lembur';
            $request->merge(['tipe' => 'masuk_lembur']);
        }

        $tanggalRecord = $sekarang->toDateString();

        // Deteksi Shift Malam (checkout untuk shift kemarin maksimal jam 07:00)
        // Jika lewat jam 07:00 dan tidak absen keluar, maka data gantung dan tidak digenerate data keluar/lemburnya.
        if ($sekarang->hour < 7 && in_array($request->tipe, ['keluar', 'keluar_lembur'])) {
            $kemarin = $sekarang->copy()->subDay()->toDateString();
            $cekGantung = Absensi::where('id_staf', $staf->id_staf)
                ->where('tanggal', $kemarin)
                ->where('tipe_absen', $tipeAbsen)
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_keluar')
                ->exists();

            if ($cekGantung) $tanggalRecord = $kemarin;
        }

        $absensi = Absensi::firstOrNew([
            'id_staf' => $staf->id_staf,
            'tanggal' => $tanggalRecord,
            'tipe_absen' => $tipeAbsen
        ]);

        $pathFoto = $this->simpanFotoBase64($request->foto, $staf->id_staf, $request->tipe);

        if (in_array($request->tipe, ['masuk', 'masuk_lembur'])) {
            if ($absensi->jam_masuk) return redirect()->back()->with('error', "Anda sudah absen masuk {$tipeAbsen} hari ini.");

            $absensi->jam_masuk = $sekarang->toTimeString();
            $absensi->foto_masuk = $pathFoto;
            $absensi->lat_masuk = $request->lat;
            $absensi->long_masuk = $request->long;

            if ($tipeAbsen === 'Reguler') {
                $batasJamMasuk = Carbon::today()->setHour(7)->setMinute(0)->setSecond(0);
                $absensi->status = $sekarang->lessThanOrEqualTo($batasJamMasuk) ? 'Tepat Waktu' : 'Terlambat';
            } else {
                $absensi->status = 'Tepat Waktu';
                // ATURAN 2: Lupa absen keluar lembur
                // Set default keterangan "Lembur tidak jelas" saat absen masuk. Akan diubah jika nanti melakukan absen keluar.
                $absensi->keterangan = 'Lembur tidak jelas (Lupa absen keluar)';
            }
            $absensi->save();

        } else {
            if (!$absensi->jam_masuk) return redirect()->back()->with('error', "Anda belum absen masuk {$tipeAbsen}!");
            if ($absensi->jam_keluar) return redirect()->back()->with('error', "Anda sudah absen pulang {$tipeAbsen}.");

            // ATURAN 3: Absen keluar reguler nyatu dengan lembur (Melewati jam 17:00)
            // Men-trigger data lembur otomatis JIKA MEREKA BENAR-BENAR ABSEN KELUAR. (Jika bablas tidak absen, otomatis tidak tergenerate karena logika ini di dalam kondisi klik keluar).
            if ($tipeAbsen === 'Reguler' && ($sekarang->hour >= 17 || $sekarang->hour < 7)) {

                // Potong jam keluar reguler persis di 17:00
                $absensi->jam_keluar = '17:00:00';
                $absensi->foto_keluar = $pathFoto;
                $absensi->lat_keluar = $request->lat;
                $absensi->long_keluar = $request->long;
                $absensi->save();

                // Buat baris baru khusus Lembur
                $lemburOtomatis = Absensi::firstOrNew([
                    'id_staf' => $staf->id_staf,
                    'tanggal' => $tanggalRecord,
                    'tipe_absen' => 'Lembur'
                ]);

                if (!$lemburOtomatis->exists) {
                    $lemburOtomatis->jam_masuk = '17:00:00';
                    $lemburOtomatis->foto_masuk = $absensi->foto_masuk; // Pakai foto masuk pagi, atau biarkan kosong
                    $lemburOtomatis->lat_masuk = $absensi->lat_masuk;
                    $lemburOtomatis->long_masuk = $absensi->long_masuk;
                }

                $lemburOtomatis->jam_keluar = $sekarang->toTimeString();
                $lemburOtomatis->foto_keluar = $pathFoto;
                $lemburOtomatis->lat_keluar = $request->lat;
                $lemburOtomatis->long_keluar = $request->long;
                $lemburOtomatis->status = 'Tepat Waktu';
                $lemburOtomatis->keterangan = 'Lembur reguler otomatis (Selesai)';
                $lemburOtomatis->save();

                return redirect()->back()->with('success', 'Absen Keluar dan Lembur Otomatis berhasil dicatat!');
            }

            // Normal pulang (Entah itu pulang reguler jam < 17:00, atau akhiri lembur pisah)
            $absensi->jam_keluar = $sekarang->toTimeString();
            $absensi->foto_keluar = $pathFoto;
            $absensi->lat_keluar = $request->lat;
            $absensi->long_keluar = $request->long;

            // Hapus status 'tidak jelas' karena user melakukan checkout
            if ($tipeAbsen === 'Lembur') {
                $absensi->keterangan = 'Lembur Selesai';
            }

            $absensi->save();
        }

        return redirect()->back()->with('success', 'Absen berhasil dicatat!');
    }

    public function create()
    {
        $user = Auth::user();
        $stafRole = Staf::with('roleStaf')->where('user_id', $user->id)->first();

        if ($stafRole->id_role_staf !== 'ROLE-STAF-ADMIN' && $user->role !== 'admin') {
            abort(403, 'Akses Ditolak');
        }

        $stafs = Staf::with('user')->get()->map(function ($staf) {
            return [
                'value' => $staf->id_staf,
                'label' => $staf->user ? $staf->user->name : $staf->id_staf,
            ];
        });

        return Inertia::render('Staf/Absensi/Form', [
            'stafs' => $stafs,
            'absensi' => null
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $stafRole = Staf::with('roleStaf')->where('user_id', $user->id)->first();

        if ($stafRole->id_role_staf !== 'ROLE-STAF-ADMIN' && $user->role !== 'admin') {
            abort(403, 'Akses Ditolak');
        }

        $stafs = Staf::with('user')->get()->map(function ($staf) {
            return [
                'value' => $staf->id_staf,
                'label' => $staf->user ? $staf->user->name : $staf->id_staf,
            ];
        });

        $absensi = Absensi::findOrFail($id);

        return Inertia::render('Staf/Absensi/Form', [
            'stafs' => $stafs,
            'absensi' => $absensi
        ]);
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'id_staf' => 'required|exists:staf,id_staf',
            'tanggal' => 'required|date',
            'tipe_absen' => 'nullable|in:Reguler,Lembur',
            'status' => 'required|in:Tepat Waktu,Terlambat,Alpha,Izin,Sakit',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string',
        ]);

        try {
            Absensi::updateOrCreate(
                [
                    'id_staf' => $request->id_staf,
                    'tanggal' => $request->tanggal,
                    'tipe_absen' => $request->tipe_absen ?? 'Reguler'
                ],
                [
                    'jam_masuk' => $request->jam_masuk,
                    'jam_keluar' => $request->jam_keluar,
                    'status' => $request->status,
                    'keterangan' => $request->keterangan,
                ]
            );

            return redirect()->route('absensi.rekap')->with('success', 'Data absensi manual berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_staf' => 'required|exists:staf,id_staf',
            'tanggal' => 'required|date',
            'tipe_absen' => 'nullable|in:Reguler,Lembur',
            'status' => 'required|in:Tepat Waktu,Terlambat,Alpha,Izin,Sakit',
            'jam_masuk' => 'nullable|date_format:H:i:s,H:i',
            'jam_keluar' => 'nullable|date_format:H:i:s,H:i',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $absensi = Absensi::findOrFail($id);
            $absensi->update([
                'id_staf' => $request->id_staf,
                'tanggal' => $request->tanggal,
                'tipe_absen' => $request->tipe_absen ?? $absensi->tipe_absen,
                'jam_masuk' => $request->jam_masuk,
                'jam_keluar' => $request->jam_keluar,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return redirect()->route('absensi.rekap')->with('success', 'Data absensi manual berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal update absensi: ' . $e->getMessage());
        }
    }

    // --- HELPER FUNCTIONS ---
    private function hitungJarakMeter($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344) * 1000; // Convert to meters
    }

    private function simpanFotoBase64($base64String, $id_staf, $tipe) {
        $image_parts = explode(";base64,", $base64String);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $id_staf . '_' . date('Ymd_His') . '_' . $tipe . '.jpg';
        $path = 'absensi/' . $fileName;
        Storage::disk('public')->put($path, $image_base64);
        return $path;
    }
}
