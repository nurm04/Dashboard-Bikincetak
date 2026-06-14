<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PesanController;
use App\Http\Controllers\Api\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

});
