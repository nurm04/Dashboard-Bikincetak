<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
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
            'id_alamat' => 'required|string',
            'items'     => 'required|array',
            'courier'   => 'required|string'
        ]);

        $totalBerat = 0;
        foreach ($request->items as $item) {
            if (isset($item['id_sku']) && $item['id_sku'] === 'PRD-0001-SKU-001') {
                $totalBerat += $item['total_berat'] ?? $item['total_berat_snapshot'] ?? $item['berat'] ?? 0;
                continue;
            }

            $finishing = isset($item['finishings']) ? (is_array($item['finishings']) ? $item['finishings'] : json_decode($item['finishings'], true)) : [];
            $finishingIds = collect($finishing)->pluck('id_sku_finishing')->toArray();

            $totalBerat += PesanService::hitungBeratTotalItem(
                $item['id_sku'],
                $item['jumlah'],
                $finishingIds
            );
        }

        $totalBerat = max($totalBerat, 1000);

        $alamat = Alamat::find($request->id_alamat);
        if (!$alamat) {
            return response()->json(['message' => 'Alamat tidak ditemukan'], 404);
        }

        $originId = config('rajaongkir.id_kecamatan_toko', 5890);

        try {
            $costs = $rajaOngkir->calculateCost(
                $originId,
                $alamat->kecamatan_id,
                $totalBerat,
                $request->courier
            );

            return response()->json($costs);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal terhubung ke server logistik.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
