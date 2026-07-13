<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Pesan;
use App\Services\PesanService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function getProvinces(RajaOngkirService $rajaOngkir)
    {
        return response()->json($rajaOngkir->getProvinces());
    }

    public function getCities($provinceId, RajaOngkirService $rajaOngkir)
    {
        return response()->json($rajaOngkir->getCities($provinceId));
    }

    public function getDistricts($cityId, RajaOngkirService $rajaOngkir)
    {
        return response()->json($rajaOngkir->getDistricts($cityId));
    }

    public function cekOngkir(Request $request, RajaOngkirService $rajaOngkir)
    {
        $request->validate([
            'id_alamat' => 'required|string|exists:alamat,id_alamat'
        ]);

        $customerId = $request->user()->customer->id_customer;

        $cart = Pesan::with('pesananItem')->where('id_customer', $customerId)
                    ->where('status_operasional', 'keranjang')
                    ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.'
            ], 404);
        }

        $totalBerat = PesanService::hitungTotalBeratPesanan($cart);

        if ($totalBerat < 1000) {
            $totalBerat = 1000;
        }

        $alamat = Alamat::find($request->id_alamat);

        $originId = config('rajaongkir.id_kecamatan_toko') ?? env('ID_KECAMATAN_TOKO');

        if (!$originId) {
            $originId = 5890;
        }

        try {
            $costs = $rajaOngkir->calculateCost($originId, $alamat->kecamatan_id, $totalBerat);

            return response()->json($costs);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke server logistik.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
