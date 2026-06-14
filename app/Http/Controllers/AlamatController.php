<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AlamatController extends Controller
{
    public function index($id_customer)
    {
        $customer = Customer::with([
            'user',
            'alamat'
        ])->findOrFail($id_customer);

        return Inertia::render('Customer/Alamat', [
            'customer' => $customer
        ]);
    }

    public function store(Request $request, $id_customer)
    {
        $customer = Customer::findOrFail($id_customer);

        $request->validate([
            'label' => 'nullable|string|max:100',
            'nama_penerima' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',

            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'nullable|string|max:255',

            'kode_pos' => 'required|string|max:10',

            'alamat_lengkap' => 'required|string',

            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            'is_default' => 'boolean',
        ]);

        try {

            DB::beginTransaction();

            if ($request->boolean('is_default')) {
                Alamat::where(
                    'id_customer',
                    $customer->id_customer
                )->update([
                    'is_default' => false
                ]);
            }

            Alamat::create([
                'id_alamat' => 'ALM-' . date('Ymd') . '-' . strtoupper(Str::random(5)),

                'id_customer' => $customer->id_customer,

                'label' => $request->label,

                'nama_penerima' => $request->nama_penerima,
                'no_hp' => $request->no_hp,

                'provinsi' => $request->provinsi,
                'kota' => $request->kota,
                'kecamatan' => $request->kecamatan,
                'kelurahan' => $request->kelurahan,

                'kode_pos' => $request->kode_pos,

                'alamat_lengkap' => $request->alamat_lengkap,

                'latitude' => $request->latitude,
                'longitude' => $request->longitude,

                'is_default' => $request->boolean('is_default'),
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Alamat berhasil ditambahkan.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Gagal menambahkan alamat: ' . $e->getMessage()
            );
        }
    }

    public function update(Request $request, $id_alamat)
    {
        $alamat = Alamat::findOrFail($id_alamat);

        $request->validate([
            'label' => 'nullable|string|max:100',
            'nama_penerima' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',

            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'nullable|string|max:255',

            'kode_pos' => 'required|string|max:10',

            'alamat_lengkap' => 'required|string',

            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            'is_default' => 'boolean',
        ]);

        try {

            DB::beginTransaction();

            if ($request->boolean('is_default')) {
                Alamat::where(
                    'id_customer',
                    $alamat->id_customer
                )->update([
                    'is_default' => false
                ]);
            }

            $alamat->update([
                'label' => $request->label,

                'nama_penerima' => $request->nama_penerima,
                'no_hp' => $request->no_hp,

                'provinsi' => $request->provinsi,
                'kota' => $request->kota,
                'kecamatan' => $request->kecamatan,
                'kelurahan' => $request->kelurahan,

                'kode_pos' => $request->kode_pos,

                'alamat_lengkap' => $request->alamat_lengkap,

                'latitude' => $request->latitude,
                'longitude' => $request->longitude,

                'is_default' => $request->boolean('is_default'),
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Alamat berhasil diperbarui.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Gagal memperbarui alamat: ' . $e->getMessage()
            );
        }
    }

    public function destroy($id_alamat)
    {
        $alamat = Alamat::findOrFail($id_alamat);

        $alamat->delete();

        return back()->with(
            'success',
            'Alamat berhasil dihapus.'
        );
    }
}
