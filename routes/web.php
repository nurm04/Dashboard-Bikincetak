<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiskonCustomerController;
use App\Http\Controllers\FinishingController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\HargaBertingkatController;
use App\Http\Controllers\HargaPengerjaanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KomposisiController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\PembelianBahanController;
use App\Http\Controllers\PesanController;
use App\Http\Controllers\PilihanFinishingController;
use App\Http\Controllers\PilihanVarianController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProdukSkuController;
use App\Http\Controllers\ProdukVarianController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RoleCustomerController;
use App\Http\Controllers\RoleStafController;
use App\Http\Controllers\SkuFinishingController;
use App\Http\Controllers\StafController;
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
    Route::resource('modul', ModulController::class);
    Route::get('/hak-akses', [ModulController::class, 'index'])->name('hak-akses.index');
    Route::get('/hak-akses/{id_modul}/edit', [HakAksesController::class, 'edit'])->name('hak-akses.edit');
    Route::post('/hak-akses/{id_modul}/sync', [HakAksesController::class, 'sync'])->name('hak-akses.sync');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::patch('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');

    Route::resource('akun', AkunController::class);

    Route::resource('customer', CustomerController::class);
    Route::post('/role-customer', [RoleCustomerController::class, 'store'])->name('role-customer.store');
    Route::post('/alamat', [AlamatController::class, 'store'])->name('alamat.store');
    Route::put('/alamat/{id_alamat}', [AlamatController::class, 'update'])->name('alamat.update');
    Route::delete('/alamat/{id_alamat}', [AlamatController::class, 'destroy'])->name('alamat.destroy');

    Route::resource('staf', StafController::class);
    Route::post('/role-staf', [RoleStafController::class, 'store'])->name('role-staf.store');

    Route::resource('kategori', KategoriController::class);

    Route::resource('produk', ProdukController::class);

    Route::get('produk/{id}/varian', [ProdukController::class, 'varian'])->name('produk.varian');
    Route::post('produk/{id}/varian', [ProdukVarianController::class, 'syncVarian'])->name('produk.syncVarian');

    Route::get('produk/{id}/sku', [ProdukController::class, 'sku'])->name('produk.sku');
    Route::post('produk/{id}/sku', [ProdukSkuController::class, 'syncSku'])->name('produk.syncSku');
    Route::get('/produk/{id}/detail-sku', [ProdukController::class, 'detailSku'])->name('produk.detailSku');
    Route::get('/sku/{id_sku}/finishing', [ProdukSkuController::class, 'finishing'])->name('sku.finishing');
    Route::post('/sku/{id_sku}/finishing/sync', [SkuFinishingController::class, 'sync'])->name('sku.syncFinishing');
    Route::get('/sku/{id_sku}/harga-bertingkat', [ProdukSkuController::class, 'hargaBertingkat'])->name('sku.hargaBertingkat');
    Route::post('/sku/{id_sku}/harga-bertingkat/sync', [HargaBertingkatController::class, 'sync'])->name('sku.syncHargaBertingkat');
    Route::get('/sku/{id_sku}/harga-pengerjaan', [ProdukSkuController::class, 'hargaPengerjaan'])->name('sku.hargaPengerjaan');
    Route::post('/sku/{id_sku}/harga-pengerjaan/sync', [HargaPengerjaanController::class, 'sync'])->name('sku.syncHargaPengerjaan');
    Route::get('/sku/{id_sku}/diskon-customer', [ProdukSkuController::class, 'diskonCustomer'])->name('sku.diskonCustomer');
    Route::post('/sku/{id_sku}/diskon-customer/sync', [DiskonCustomerController::class, 'sync'])->name('sku.syncdiskonCustomer');
    Route::get('/sku/{id_sku}/komposisi', [ProdukSkuController::class, 'komposisi'])->name('sku.komposisi');
    Route::post('/sku/{id_sku}/komposisi/sync', [KomposisiController::class, 'sync'])->name('sku.syncKomposisi');
    Route::delete('/sku/{id_sku}', [ProdukSkuController::class, 'destroy'])->name('sku.destroy');

    Route::resource('varian', VarianController::class);
    Route::post('/pilihan-varian', [PilihanVarianController::class, 'store'])->name('pilihan-varian.store');
    Route::put('/pilihan-varian/{id}', [PilihanVarianController::class, 'update'])->name('pilihan-varian.update');
    Route::delete('/pilihan-varian/{id}', [PilihanVarianController::class, 'destroy'])->name('pilihan-varian.destroy');

    Route::resource('finishing', FinishingController::class);
    Route::post('/pilihan-finishing', [PilihanFinishingController::class, 'store'])->name('pilihan-finishing.store');
    Route::put('/pilihan-finishing/{id}', [PilihanFinishingController::class, 'update'])->name('pilihan-finishing.update');
    Route::delete('/pilihan-finishing/{id}', [PilihanFinishingController::class, 'destroy'])->name('pilihan-finishing.destroy');

    Route::resource('bahan-baku', BahanBakuController::class);
    Route::resource('pembelian-bahan', PembelianBahanController::class);

    Route::get('pesan', [PesanController::class, 'index'])->name('pesan.index');
    Route::post('pesan', [PesanController::class, 'store'])->name('pesan.store');
    Route::get('pesan/{id}/detail', [PesanController::class, 'detail'])->name('pesan.detail');
    Route::put('pesan/{id}/operasional', [PesanController::class, 'updateOperasional'])->name('pesan.updateOperasional');
    Route::put('pesan/{id}/pembayaran', [PesanController::class, 'updatePembayaran'])->name('pesan.updatePembayaran');

    Route::get('/pos-kasir', [ProdukController::class, 'katalogWeb'])->name('pos.katalog');
    Route::get('/pos-kasir/produk/{id_produk}', [ProdukController::class, 'detailKatalogWeb'])->name('pos.detail');
});

require __DIR__.'/auth.php';
