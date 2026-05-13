<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiskonCustomerController;
use App\Http\Controllers\HargaBertingkatController;
use App\Http\Controllers\HargaPengerjaanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PilihanVarianController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProdukSkuController;
use App\Http\Controllers\ProdukVarianController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RoleCustomerController;
use App\Http\Controllers\VarianController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::patch('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');

    Route::resource('akun', AkunController::class);

    Route::resource('customer', CustomerController::class);
    Route::post('/role-customer', [RoleCustomerController::class, 'store'])->name('role-customer.store');

    Route::resource('kategori', KategoriController::class);

    Route::resource('produk', ProdukController::class);

    Route::get('produk/{id}/varian', [ProdukController::class, 'varian'])->name('produk.varian');
    Route::post('produk/{id}/varian', [ProdukVarianController::class, 'syncVarian'])->name('produk.syncVarian');

    Route::get('produk/{id}/sku', [ProdukController::class, 'sku'])->name('produk.sku');
    Route::post('produk/{id}/sku', [ProdukSkuController::class, 'syncSku'])->name('produk.syncSku');
    Route::get('/produk/{id}/detail-sku', [ProdukController::class, 'detailSku'])->name('produk.detailSku');
    Route::get('/sku/{id_sku}/harga-bertingkat', [ProdukSkuController::class, 'hargaBertingkat'])->name('sku.hargaBertingkat');
    Route::post('/sku/{id_sku}/harga-bertingkat/sync', [HargaBertingkatController::class, 'sync'])->name('sku.syncHargaBertingkat');
    Route::get('/sku/{id_sku}/harga-pengerjaan', [ProdukSkuController::class, 'hargaPengerjaan'])->name('sku.hargaPengerjaan');
    Route::post('/sku/{id_sku}/harga-pengerjaan/sync', [HargaPengerjaanController::class, 'sync'])->name('sku.syncHargaPengerjaan');
    Route::get('/sku/{id_sku}/diskon-customer', [ProdukSkuController::class, 'diskonCustomer'])->name('sku.diskonCustomer');
    Route::post('/sku/{id_sku}/diskon-customer/sync', [DiskonCustomerController::class, 'sync'])->name('sku.syncdiskonCustomer');
    Route::delete('/sku/{id_sku}', [ProdukSkuController::class, 'destroy'])->name('sku.destroy');

    Route::resource('varian', VarianController::class);
    Route::post('/pilihan-varian', [PilihanVarianController::class, 'store'])->name('pilihan-varian.store');
    Route::put('/pilihan-varian/{id}', [PilihanVarianController::class, 'update'])->name('pilihan-varian.update');
    Route::delete('/pilihan-varian/{id}', [PilihanVarianController::class, 'destroy'])->name('pilihan-varian.destroy');
    });

require __DIR__.'/auth.php';
