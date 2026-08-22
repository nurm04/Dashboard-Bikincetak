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
        $hariIni = Carbon::now()->toDateString();

        $absensiHariIni = Absensi::where('id_staf', $staf->id_staf)
                                 ->where('tanggal', $hariIni)
                                 ->first();

        return Inertia::render('Staf/Absensi/Index', [
            'absensi' => $absensiHariIni,
            'office_lat' => $this->officeLat,
            'office_long' => $this->officeLong,
            'max_radius' => $this->maxRadius,
        ]);
    }

    public function rekap(Request $request)
    {
        // 1. Pastikan cuma Admin yang bisa akses (Security Check)
        $user = Auth::user();
        $staf = Staf::with('roleStaf')->where('user_id', $user->id)->firstOrFail();

        // Sesuaikan dengan logic pengecekan role lu
        if ($staf->id_role_staf !== 'ROLE-STAF-ADMIN' && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // 2. Ambil parameter pencarian dan filter bulan
        $search = $request->input('search');
        $bulan = $request->input('bulan', Carbon::now()->format('m'));
        $tahun = $request->input('tahun', Carbon::now()->format('Y'));

        // 3. Query Data Absensi dengan Relasi Staf & User
        $absensis = Absensi::with(['staf.user', 'staf.roleStaf'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($search, function ($query, $search) {
                $query->whereHas('staf.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_staf', 'asc')
            ->paginate(15) // Kita pakai pagination biar tabel gak berat
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
            'tipe' => 'required|in:masuk,keluar',
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

        $pathFoto = $this->simpanFotoBase64($request->foto, $staf->id_staf, $request->tipe);

        $absensi = Absensi::firstOrNew([
            'id_staf' => $staf->id_staf,
            'tanggal' => $sekarang->toDateString()
        ]);

        if ($request->tipe === 'masuk') {
            if ($absensi->jam_masuk) return redirect()->back()->with('error', 'Anda sudah absen masuk hari ini.');

            $absensi->jam_masuk = $sekarang->toTimeString();
            $absensi->foto_masuk = $pathFoto;
            $absensi->lat_masuk = $request->lat;
            $absensi->long_masuk = $request->long;

            $batasJamMasuk = Carbon::today()->setHour(7)->setMinute(0)->setSecond(0);
            $absensi->status = $sekarang->lessThanOrEqualTo($batasJamMasuk) ? 'Tepat Waktu' : 'Terlambat';
            $absensi->keterangan = '';
        } else {
            if (!$absensi->jam_masuk) return redirect()->back()->with('error', 'Anda belum absen masuk!');
            if ($absensi->jam_keluar) return redirect()->back()->with('error', 'Anda sudah absen pulang.');

            $absensi->jam_keluar = $sekarang->toTimeString();
            $absensi->foto_keluar = $pathFoto;
            $absensi->lat_keluar = $request->lat;
            $absensi->long_keluar = $request->long;
        }

        $absensi->save();

        return redirect()->back()->with('success', 'Absen ' . ucfirst($request->tipe) . ' berhasil dicatat!');
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
            'status' => 'required|in:Tepat Waktu,Terlambat,Alpha,Izin,Sakit',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string',
        ]);

        try {
            Absensi::updateOrCreate(
                ['id_staf' => $request->id_staf, 'tanggal' => $request->tanggal],
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
