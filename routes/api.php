<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KeranjangController;
use Illuminate\Http\Request;

// Route::get('/katalog', [ProdukController::class, 'katalogWeb']);
// Route::get('/katalog/produk/{id_produk}', [ProdukController::class, 'detailKatalogWeb']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/items', [ProdukController::class, 'getAllItems']);
Route::get('/item/{id}', [ProdukController::class, 'getDetailItem']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('customer')
        ]);
    });

    Route::get('/cart', [KeranjangController::class, 'index']);
    Route::post('/cart', [KeranjangController::class, 'store']);
    Route::put('/cart/{id}', [KeranjangController::class, 'update']);
    Route::delete('/cart/{id}', [KeranjangController::class, 'destroy']);

});
