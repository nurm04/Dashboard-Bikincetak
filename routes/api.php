<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;

Route::get('/katalog', [ProdukController::class, 'katalogWeb']);
Route::get('/katalog/produk/{id_produk}', [ProdukController::class, 'detailKatalogWeb']);
