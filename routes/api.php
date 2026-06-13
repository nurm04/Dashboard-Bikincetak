<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PesanController;
use Illuminate\Http\Request;

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

    Route::get('/cart', [PesanController::class, 'getCart']);
    Route::post('/cart', [PesanController::class, 'addCart']);
    Route::patch('/cart/item/{id}', [PesanController::class, 'updateCart']);
    Route::delete('/cart/item/{id}', [PesanController::class, 'destroyCart']);

});
