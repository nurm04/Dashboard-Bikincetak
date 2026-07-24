<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $vendors = Vendor::with('user')
            ->when($search, function ($query, $search) {
                $query->where('nama_vendor', 'like', "%{$search}%")
                      ->orWhere('nama_pic', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return Inertia::render('Vendor/Index', [
            'vendors' => $vendors,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Vendor/Form');
    }

    public function edit($id_vendor)
    {
        $vendor = Vendor::with('user')->findOrFail($id_vendor);

        return Inertia::render('Vendor/Form', [
            'vendor' => $vendor
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:150',
            'nama_pic' => 'nullable|string|max:100',
            'no_hp' => 'required|string|max:20',
            'alamat_lengkap' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'nama_bank' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:50',
            'atas_nama' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->nama_vendor,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'vendor',
            ]);


            $date = date('Ymd');
            $lastVendor = Vendor::where('id_vendor', 'like', "VND-{$date}-%")->orderBy('id_vendor', 'desc')->first();
            $urutan = $lastVendor ? intval(substr($lastVendor->id_vendor, -3)) + 1 : 1;
            $id_vendor = "VND-{$date}-" . str_pad($urutan, 3, '0', STR_PAD_LEFT);


            Vendor::create([
                'id_vendor' => $id_vendor,
                'user_id' => $user->id,
                'nama_vendor' => $request->nama_vendor,
                'nama_pic' => $request->nama_pic,
                'no_hp' => $request->no_hp,
                'alamat_lengkap' => $request->alamat_lengkap,
                'nama_bank' => $request->nama_bank,
                'no_rekening' => $request->no_rekening,
                'atas_nama' => $request->atas_nama,
                'is_active' => true,
            ]);

            DB::commit();
            return back()->with('success', 'Data Vendor dan Akun berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal tambah vendor: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan vendor: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id_vendor)
    {
        $vendor = Vendor::with('user')->findOrFail($id_vendor);

        $request->validate([
            'nama_vendor' => 'required|string|max:150',
            'nama_pic' => 'nullable|string|max:100',
            'no_hp' => 'required|string|max:20',
            'alamat_lengkap' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $vendor->user_id,
            'password' => 'nullable|string|min:6',
            'nama_bank' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:50',
            'atas_nama' => 'nullable|string|max:100',
            'is_active' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->nama_vendor,
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $vendor->user->update($userData);

            $vendor->update([
                'nama_vendor' => $request->nama_vendor,
                'nama_pic' => $request->nama_pic,
                'no_hp' => $request->no_hp,
                'alamat_lengkap' => $request->alamat_lengkap,
                'nama_bank' => $request->nama_bank,
                'no_rekening' => $request->no_rekening,
                'atas_nama' => $request->atas_nama,
                'is_active' => $request->is_active,
            ]);

            DB::commit();
            return back()->with('success', 'Data Vendor berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update vendor: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui vendor: ' . $e->getMessage());
        }
    }


    public function destroy($id_vendor)
    {
        try {
            DB::beginTransaction();

            $vendor = Vendor::findOrFail($id_vendor);
            $userId = $vendor->user_id;

            $vendor->delete();

            User::destroy($userId);

            DB::commit();
            return back()->with('success', 'Data Vendor berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus vendor: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus vendor, pastikan tidak ada pesanan yang terikat.');
        }
    }
}
