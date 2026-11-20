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
    // private $officeLat = -7.127083;
    // private $officeLong = 112.721294;
    private $officeLat = -7.304226;
    private $officeLong = 112.756378;
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
        $jamAktual = $sekarang->format('H:i:s');
        $jamAngka = $sekarang->hour;

        // Tentukan tanggal record (jika shift malam / dini hari < 07:00, catat ke tanggal kemarin)
        $tanggalRecord = $sekarang->toDateString();
        if ($sekarang->hour < 7 && in_array($request->tipe, ['keluar', 'keluar_lembur'])) {
            $tanggalRecord = $sekarang->copy()->subDay()->toDateString();
        }

        // ==========================================
        // 1. PROSES ABSEN MASUK (REGULER / LEMBUR)
        // ==========================================
        if (in_array($request->tipe, ['masuk', 'masuk_lembur'])) {
            // Jika hari Minggu atau datang di atas jam 17:00 atau di bawah jam 07:00 pagi langsung dianggap Masuk Lembur
            $tipeAbsen = ($isMinggu || $jamAngka >= 17 || $jamAngka < 7) ? 'Lembur' : 'Reguler';

            $absensi = Absensi::firstOrNew([
                'id_staf' => $staf->id_staf,
                'tanggal' => $tanggalRecord,
                'tipe_absen' => $tipeAbsen
            ]);

            if ($absensi->jam_masuk) {
                return redirect()->back()->with('error', "Anda sudah absen masuk {$tipeAbsen} hari ini.");
            }

            $pathFoto = $this->simpanFotoBase64($request->foto, $staf->id_staf, $request->tipe);

            $absensi->jam_masuk = $jamAktual;
            $absensi->foto_masuk = $pathFoto;
            $absensi->lat_masuk = $request->lat;
            $absensi->long_masuk = $request->long;

            if ($tipeAbsen === 'Reguler') {
                $batasJamMasuk = Carbon::today()->setHour(7)->setMinute(0)->setSecond(0);
                $absensi->status = $sekarang->lessThanOrEqualTo($batasJamMasuk) ? 'Tepat Waktu' : 'Terlambat';
                $absensi->keterangan = '-';
            } else {
                $absensi->status = 'Tepat Waktu';
                $absensi->keterangan = 'Lembur (Belum selesai)';
            }
            $absensi->save();

            return redirect()->back()->with('success', "Absen masuk {$tipeAbsen} berhasil dicatat!");
        }

        // ==========================================
        // 2. PROSES ABSEN KELUAR / PULANG
        // ==========================================
        if (in_array($request->tipe, ['keluar', 'keluar_lembur'])) {

            // Cek apakah ada absen reguler yang menggantung (belum checkout)
            $absensiReguler = Absensi::where('id_staf', $staf->id_staf)
                ->where('tanggal', $tanggalRecord)
                ->where('tipe_absen', 'Reguler')
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_keluar')
                ->first();

            if ($absensiReguler) {
                // Jika staf pulang lewat dari jam 17:00 (atau dini hari sebelum jam 07:00)
                if ($jamAngka >= 17 || $jamAngka < 7) {
                    // 1. Tutup jam keluar reguler tepat pukul 17:00:00
                    $pathFoto = $this->simpanFotoBase64($request->foto, $staf->id_staf, 'keluar_reguler');

                    $absensiReguler->jam_keluar = '17:00:00';
                    $absensiReguler->foto_keluar = $pathFoto;
                    $absensiReguler->lat_keluar = $request->lat;
                    $absensiReguler->long_keluar = $request->long;
                    $absensiReguler->save();

                    // 2. Otomatis buatkan data Absensi Lembur baru mulai pukul 17:00:00 hingga jam aktual sekarang
                    $absensiLembur = Absensi::firstOrNew([
                        'id_staf' => $staf->id_staf,
                        'tanggal' => $tanggalRecord,
                        'tipe_absen' => 'Lembur'
                    ]);

                    $absensiLembur->jam_masuk = '17:00:00';
                    $absensiLembur->status = 'Tepat Waktu';
                    $absensiLembur->jam_keluar = $jamAktual;
                    $absensiLembur->foto_keluar = $pathFoto;
                    $absensiLembur->lat_keluar = $request->lat;
                    $absensiLembur->long_keluar = $request->long;
                    $absensiLembur->keterangan = 'Lembur Otomatis Selesai';
                    $absensiLembur->save();

                    return redirect()->back()->with('success', 'Absen pulang berhasil. Jam kerja setelah 17:00 otomatis tercatat sebagai lembur!');
                } else {
                    // Pulang normal di bawah jam 17:00
                    $pathFoto = $this->simpanFotoBase64($request->foto, $staf->id_staf, 'keluar');

                    $absensiReguler->jam_keluar = $jamAktual;
                    $absensiReguler->foto_keluar = $pathFoto;
                    $absensiReguler->lat_keluar = $request->lat;
                    $absensiReguler->long_keluar = $request->long;
                    $absensiReguler->save();

                    return redirect()->back()->with('success', 'Absen pulang reguler berhasil dicatat!');
                }
            }

            // Jika yang ingin di-checkout adalah sesi Lembur (misal staf sudah mulai lembur terpisah)
            $absensiLembur = Absensi::where('id_staf', $staf->id_staf)
                ->where('tanggal', $tanggalRecord)
                ->where('tipe_absen', 'Lembur')
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_keluar')
                ->first();

            if ($absensiLembur) {
                $pathFoto = $this->simpanFotoBase64($request->foto, $staf->id_staf, 'keluar_lembur');

                $absensiLembur->jam_keluar = $jamAktual;
                $absensiLembur->foto_keluar = $pathFoto;
                $absensiLembur->lat_keluar = $request->lat;
                $absensiLembur->long_keluar = $request->long;
                $absensiLembur->keterangan = 'Lembur Selesai';
                $absensiLembur->save();

                return redirect()->back()->with('success', 'Absen selesai lembur berhasil dicatat!');
            }

            return redirect()->back()->with('error', 'Tidak ada data jam masuk aktif yang ditemukan untuk di-checkout.');
        }

        return redirect()->back()->with('error', 'Aksi tidak valid.');
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
        return ($miles * 1.609344) * 1000;
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
