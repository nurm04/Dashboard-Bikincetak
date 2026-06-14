<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlamatController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $alamat = Alamat::where(
            'id_customer',
            $customer->id_customer
        )
        ->orderByDesc('is_default')
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $alamat
        ]);
    }

    public function show(Request $request, $id_alamat)
    {
        $customer = $request->user()->customer;

        $alamat = Alamat::where(
            'id_customer',
            $customer->id_customer
        )
        ->findOrFail($id_alamat);

        return response()->json([
            'success' => true,
            'data' => $alamat
        ]);
    }

    public function store(Request $request)
    {
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

            'is_default' => 'boolean'
        ]);

        DB::beginTransaction();

        try {

            $customer = $request->user()->customer;

            if ($request->boolean('is_default')) {
                Alamat::where(
                    'id_customer',
                    $customer->id_customer
                )->update([
                    'is_default' => false
                ]);
            }

            $alamat = Alamat::create([
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

            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil ditambahkan.',
                'data' => $alamat
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id_alamat)
    {
        $customer = $request->user()->customer;

        $alamat = Alamat::where(
            'id_customer',
            $customer->id_customer
        )->findOrFail($id_alamat);

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

            'is_default' => 'boolean'
        ]);

        DB::beginTransaction();

        try {

            if ($request->boolean('is_default')) {
                Alamat::where(
                    'id_customer',
                    $customer->id_customer
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

            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil diperbarui.',
                'data' => $alamat->fresh()
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id_alamat)
    {
        $customer = $request->user()->customer;

        $alamat = Alamat::where(
            'id_customer',
            $customer->id_customer
        )->findOrFail($id_alamat);

        $alamat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus.'
        ]);
    }

    public function setDefault(Request $request, $id_alamat)
    {
        $customer = $request->user()->customer;

        $alamat = Alamat::where(
            'id_customer',
            $customer->id_customer
        )->findOrFail($id_alamat);

        DB::transaction(function () use ($alamat, $customer) {

            Alamat::where(
                'id_customer',
                $customer->id_customer
            )->update([
                'is_default' => false
            ]);

            $alamat->update([
                'is_default' => true
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diubah.'
        ]);
    }
}
