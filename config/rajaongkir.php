<?php
    return [
        'api_key' => env('RAJAONGKIR_API_KEY', ''),
        'base_url' => env('RAJAONGKIR_BASE_URL', ''),
        'komerce_v2_sandbox' => env('KOMERCE_V2_SANDBOX_URL', 'https://api-sandbox.collaborator.komerce.id'),
        'komerce_v2_live' => env('KOMERCE_V2_LIVE_URL', 'https://api.collaborator.komerce.id'),
        'id_kecamatan_toko' => env('ID_KECAMATAN_TOKO', '5890'),
    ];
