<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PesanController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/items', [ProdukController::class, 'getAllItems']);
Route::get('/item/{id}', [ProdukController::class, 'getDetailItem']);

Route::get('/shipping/provinces', [ShippingController::class, 'getProvinces']);
Route::get('/shipping/cities/{provinceId}', [ShippingController::class, 'getCities']);
Route::get('/shipping/districts/{cityId}', [ShippingController::class, 'getDistricts']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('customer')
        ]);
    });
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'updatePassword']);

    Route::get('/alamat', [AlamatController::class, 'index']);
    Route::get('/alamat/{id_alamat}', [AlamatController::class, 'show']);
    Route::post('/alamat', [AlamatController::class, 'store']);
    Route::put('/alamat/{id_alamat}', [AlamatController::class, 'update']);
    Route::delete('/alamat/{id_alamat}', [AlamatController::class, 'destroy']);
    Route::patch('/alamat/{id_alamat}/default', [AlamatController::class, 'setDefault']);

    Route::get('/cart', [PesanController::class, 'getCart']);
    Route::post('/cart', [PesanController::class, 'addCart']);
    Route::patch('/cart/item/{id}', [PesanController::class, 'updateCart']);
    Route::delete('/cart/item/{id}', [PesanController::class, 'destroyCart']);
    Route::post('/cart/checkout', [PesanController::class, 'checkoutCart']);
    Route::get('/pesanan', [PesanController::class, 'getPesanan']);
    Route::get('/pesanan/{kode_transaksi}', [PesanController::class, 'getPesananByKodeTransaksi']);
    Route::patch('/pesanan/{id_pesan}/cancel', [PesanController::class, 'cancelPesanan']);
    Route::put('/pesanan/{id_pesan}/selesai', [PesanController::class, 'pesananDiterimaPelanggan']);

    Route::post('/shipping/cost', [ShippingController::class, 'cekOngkir']);

    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::get('/vouchers/{kode}', [VoucherController::class, 'cekVoucher']);
});
