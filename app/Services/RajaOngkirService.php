<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected $baseUrl;
    protected $apiKey;
    protected $komerceV2Url;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('rajaongkir.base_url'), '/');
        $this->apiKey = config('rajaongkir.api_key');

        $isProduction = env('APP_ENV') === 'production';
        $this->komerceV2Url = $isProduction ? config('rajaongkir.komerce_v2_live') : config('rajaongkir.komerce_v2_sandbox');
    }

    public function getProvinces()
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
                        ->get($this->baseUrl . '/destination/province');

        return $response->json();
    }

    public function getCities($provinceId)
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
                        ->get($this->baseUrl . '/destination/city/' . $provinceId);

        return $response->json();
    }

    public function getDistricts($cityId)
    {
        $response = Http::withHeaders(['key' => $this->apiKey])
                        ->get($this->baseUrl . '/destination/district/' . $cityId);

        return $response->json();
    }

    public function calculateCost($originId, $destinationId, $weight, $requestedCourier = null)
    {
        $couriers = $requestedCourier ? [$requestedCourier] : ['jne', 'sicepat', 'jnt', 'pos'];

        $allResults = [];
        $meta = null;

        foreach ($couriers as $courier) {
            $response = Http::asForm()
                            ->withHeaders(['key' => $this->apiKey])
                            ->post($this->baseUrl . '/calculate/domestic-cost', [
                                'origin'      => (string) $originId,
                                'destination' => (string) $destinationId,
                                'weight'      => (int) $weight,
                                'courier'     => $courier
                            ]);

            $result = $response->json();

            if (isset($result['data']) && is_array($result['data'])) {
                $allResults = array_merge($allResults, $result['data']);
            }

            if (!$meta && isset($result['meta'])) {
                $meta = $result['meta'];
            }
        }

        if (empty($allResults) && isset($result)) {
            return $result;
        }

        return [
            'meta' => $meta ?? ['status' => 'success', 'code' => 200, 'message' => 'Success'],
            'data' => $allResults
        ];
    }

    public function requestPickup($pesanan)
    {
        if (!$pesanan->ekspedisi_nama || !$pesanan->ekspedisi_layanan) {
            return null;
        }

        $totalBeratPesanan = $pesanan->pesananItem->sum('total_berat_snapshot') ?: 1000;
        $totalNilaiBarang = $pesanan->pesananItem->sum(function ($item) {
            return $item->harga_satuan_snapshot * $item->jumlah;
        });

        $payloadStoreOrder = [
            'partner_id'          => env('KOMERCE_PARTNER_ID', ''),
            'origin'              => config('rajaongkir.id_kecamatan_toko'),
            'destination'         => $pesanan->alamat->kecamatan_id ?? null,
            'courier'             => preg_match('/\((.*?)\)/', $pesanan->ekspedisi_nama, $match)
                                        ? strtolower(str_replace('&', 'n', $match[1]))
                                        : strtolower(explode(' ', $pesanan->ekspedisi_nama)[0]),
            'service'             => $pesanan->ekspedisi_layanan,
            'weight'              => $totalBeratPesanan,
            'item_name'           => "Pesanan Cetak #" . $pesanan->id_pesan,
            'item_value'          => $totalNilaiBarang,
            'customer_name'       => $pesanan->alamat->nama_penerima,
            'customer_phone'      => $pesanan->alamat->no_hp,
            'customer_address'    => $pesanan->alamat->alamat_lengkap . ', ' . $pesanan->alamat->kecamatan . ', ' . $pesanan->alamat->kota,
            'customer_zip_code'   => $pesanan->alamat->kode_pos,
            'is_insurance'        => 0,
            'payment_method'      => 'NON-COD',
        ];

        $responseOrder = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Accept'    => 'application/json'
        ])->post($this->komerceV2Url . '/order/api/v1/orders/store', $payloadStoreOrder);

        if ($responseOrder->successful()) {
            $hasilOrder = $responseOrder->json();

            if (isset($hasilOrder['data']['awb'])) {
                $awb = $hasilOrder['data']['awb'];

                if (isset($hasilOrder['data']['order_id'])) {
                    Http::withHeaders([
                        'x-api-key' => $this->apiKey,
                        'Accept'    => 'application/json'
                    ])->post($this->komerceV2Url . '/order/api/v1/pickup/request', [
                        'order_id' => $hasilOrder['data']['order_id']
                    ]);
                }

                return $awb;
            }
        } else {
            Log::warning("Gagal Store Order Komerce untuk Pesanan {$pesanan->id_pesan}", [
                'url'      => $this->komerceV2Url . '/order/api/v1/orders/store',
                'response' => $responseOrder->json(),
                'payload'  => $payloadStoreOrder
            ]);
        }

        return null;
    }
}
