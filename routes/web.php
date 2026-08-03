<?php
use App\Http\Controllers\Web\AkunController;
use App\Http\Controllers\Web\BahanBakuController;
use App\Http\Controllers\Web\BukuBesarController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FileManageController;
use App\Http\Controllers\Web\GlobalSearchController;
use App\Http\Controllers\Web\HakAksesController;
use App\Http\Controllers\Web\ModulController;
use App\Http\Controllers\Web\PembayaranController;
use App\Http\Controllers\Web\PembelianBahanController;
use App\Http\Controllers\Web\PesanController;
use App\Http\Controllers\Web\Produk\DiskonCustomerController;
use App\Http\Controllers\Web\Produk\FinishingController;
use App\Http\Controllers\Web\Produk\HargaBertingkatController;
use App\Http\Controllers\Web\Produk\HargaPengerjaanController;
use App\Http\Controllers\Web\Produk\KategoriController;
use App\Http\Controllers\Web\Produk\KomposisiController;
use App\Http\Controllers\Web\Produk\PilihanFinishingController;
use App\Http\Controllers\Web\Produk\PilihanVarianController;
use App\Http\Controllers\Web\Produk\ProdukController;
use App\Http\Controllers\Web\Produk\ProdukSkuController;
use App\Http\Controllers\Web\Produk\ProdukVarianController;
use App\Http\Controllers\Web\Produk\SkuFinishingController;
use App\Http\Controllers\Web\Produk\VarianController;
use App\Http\Controllers\Web\ProduksiController;
use App\Http\Controllers\Web\ProfilController;
use App\Http\Controllers\Web\ShippingController;
use App\Http\Controllers\Web\TagihanVendorController;
use App\Http\Controllers\Web\User\AlamatController;
use App\Http\Controllers\Web\User\CustomerController;
use App\Http\Controllers\Web\User\RoleCustomerController;
use App\Http\Controllers\Web\User\RoleStafController;
use App\Http\Controllers\Web\User\StafController;
use App\Http\Controllers\Web\User\VendorController;
use App\Http\Controllers\Web\VoucherController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'block.vendor'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/test-notif/pesanan', function () {
        $user = auth()->user();
        
        if (!in_array($user->staf->id_role_staf ?? null, ['ROLE-STAF-ADMIN', 'ROLE-STAF-KASIR'])) {
            return 'Akses ditolak: Route ini khusus Admin dan Kasir.';
        }

        // Ambil 1 data pesanan terbaru dari database sebagai dumi
        // Pastikan model lu bener App\Models\Pesan (sesuaikan kalau beda)
        $pesan = \App\Models\Pesan::latest()->first();

        if (!$pesan) {
            return 'Gagal: Belum ada satupun data pesanan di database untuk ditest.';
        }

        // Tembak Event aslinya! Ini akan memicu Echo (WebSocket) & Listener FCM lu
        event(new \App\Events\PesananBaruEvent($pesan));

        return "Event PesananBaruEvent berhasil ditembak pakai data {$pesan->id_pesan}! Cek HP & Laptop sekarang.";
    });

    Route::get('/test-notif/produksi', function () {
        $user = auth()->user();

        if (!in_array($user->staf->id_role_staf ?? null, ['ROLE-STAF-ADMIN', 'ROLE-STAF-PRODUKSI'])) {
            return 'Akses ditolak: Route ini khusus Admin dan Produksi.';
        }

        $pesan = \App\Models\Pesan::latest()->first();

        if (!$pesan) {
            return 'Gagal: Belum ada satupun data pesanan di database untuk ditest.';
        }

        // Tembak Event aslinya!
        event(new \App\Events\ProduksiBaruEvent($pesan));

        return "Event ProduksiBaruEvent berhasil ditembak pakai data {$pesan->id_pesan}! Cek HP & Laptop sekarang.";
    });

    Route::post('/simpan-fcm-token', function (Request $request) {
        $token = $request->input('token') ?? $request->input('fcm_token');

        if ($token) {
            $user = auth()->user();
            
            $tokens = is_array($user->fcm_token) ? $user->fcm_token : [];

            if (!in_array($token, $tokens)) {
                $tokens[] = $token;
                
                $user->update([
                    'fcm_token' => $tokens
                ]);
            }

            return response()->json(['message' => 'Token berhasil disimpan!']);
        }

        return response()->json(['message' => 'Token tidak ditemukan di request'], 400);
    });

    Route::get('/shipping/provinces', [ShippingController::class, 'getProvinces']);
    Route::get('/shipping/cities/{provinceId}', [ShippingController::class, 'getCities']);
    Route::get('/shipping/districts/{cityId}', [ShippingController::class, 'getDistricts']);
    Route::post('/ongkir/calculate', [ShippingController::class, 'cekOngkir']);

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::patch('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');

    Route::get('produksi', [ProduksiController::class, 'index'])->middleware('akses:produksi')->name('produksi.index');
    Route::post('produksi/item/{id_item_produksi}/selesai', [ProduksiController::class, 'selesaikanItemProduksi'])->middleware('akses:produksi,ubah')->name('produksi.selesaikan');
    Route::get('produksi/histori', [ProduksiController::class, 'histori'])->middleware('akses:produksi')->name('produksi.histori');

    Route::middleware('block.vendor')->group(function () {
        Route::post('produksi/{id_pesan}/alokasi', [ProduksiController::class, 'alokasiProduksi'])->middleware('akses:produksi,ubah')->name('produksi.alokasi');
        Route::post('produksi/{id_pesan}/kirim', [ProduksiController::class, 'kirimPesanan'])->middleware('akses:produksi,ubah')->name('produksi.kirim');
        Route::put('/produksi/{id_pesan}/update-berat', [ProduksiController::class, 'updateBerat'])->middleware('akses:produksi,ubah')->name('produksi.update_berat');
        Route::post('/produksi/{id_pesan}/pengantaran', [ProduksiController::class, 'prosesPengantaran'])->middleware('akses:produksi,ubah')->name('produksi.pengantaran.proses');
        Route::get('/buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
        Route::resource('akun', AkunController::class)->middleware('akses:akun');
        Route::middleware('akses:customer')->group(function () {
            Route::resource('customer', CustomerController::class);
            Route::post('/role-customer', [RoleCustomerController::class, 'store'])->name('role-customer.store');
            Route::get('/customer/{id_customer}/alamat', [AlamatController::class, 'index'])->name('alamat.customer');
            Route::post('/customer/{id_customer}/alamat', [AlamatController::class, 'store'])->name('alamat.store');
            Route::put('/alamat/{id_alamat}', [AlamatController::class, 'update'])->name('alamat.update');
            Route::delete('/alamat/{id_alamat}', [AlamatController::class, 'destroy'])->name('alamat.destroy');

            Route::get('/customer/{id_customer}/password', [CustomerController::class, 'editPassword'])->middleware('akses:customer,ubah')->name('customer.password');
            Route::put('/customer/{id_customer}/password', [CustomerController::class, 'updatePassword'])->middleware('akses:customer,ubah')->name('customer.password.update');
        });
        Route::middleware('akses:staf')->group(function () {
            Route::resource('staf', StafController::class);
            Route::post('/role-staf', [RoleStafController::class, 'store'])->name('role-staf.store');
        });
        Route::resource('kategori', KategoriController::class)->middleware('akses:kategori');
        Route::middleware('akses:produk')->group(function () {
            Route::resource('produk', ProdukController::class);
            Route::get('produk/{id}/varian', [ProdukController::class, 'varian'])->middleware('akses:produk,ubah')->name('produk.varian');
            Route::post('produk/{id}/varian', [ProdukVarianController::class, 'syncVarian'])->middleware('akses:produk,ubah')->name('produk.syncVarian');
        });
        Route::middleware('akses:produk-sku')->group(function () {
            Route::post('/produk/sku/{id_produk}/import-csv', [ProdukSkuController::class, 'importCsv'])->middleware('akses:produk-sku,ubah')->name('sku.importCsv');
            Route::get('produk/{id}/sku', [ProdukController::class, 'sku'])->middleware('akses:produk-sku,ubah')->name('produk.sku');
            Route::post('produk/{id}/sku', [ProdukSkuController::class, 'syncSku'])->middleware('akses:produk-sku,ubah')->name('produk.syncSku');
            Route::get('/produk/{id}/detail-sku', [ProdukController::class, 'detailSku'])->name('produk.detailSku');
            Route::get('/sku/{id_sku}/finishing', [ProdukSkuController::class, 'finishing'])->name('sku.finishing');
            Route::post('/sku/{id_sku}/finishing/sync', [SkuFinishingController::class, 'sync'])->middleware('akses:produk-sku,ubah')->name('sku.syncFinishing');
            Route::get('/sku/{id_sku}/harga-bertingkat', [ProdukSkuController::class, 'hargaBertingkat'])->name('sku.hargaBertingkat');
            Route::post('/sku/{id_sku}/harga-bertingkat/sync', [HargaBertingkatController::class, 'sync'])->middleware('akses:produk-sku,ubah')->name('sku.syncHargaBertingkat');
            Route::get('/sku/{id_sku}/harga-pengerjaan', [ProdukSkuController::class, 'hargaPengerjaan'])->name('sku.hargaPengerjaan');
            Route::post('/sku/{id_sku}/harga-pengerjaan/sync', [HargaPengerjaanController::class, 'sync'])->middleware('akses:produk-sku,ubah')->name('sku.syncHargaPengerjaan');
            Route::get('/sku/{id_sku}/diskon-customer', [ProdukSkuController::class, 'diskonCustomer'])->name('sku.diskonCustomer');
            Route::post('/sku/{id_sku}/diskon-customer/sync', [DiskonCustomerController::class, 'sync'])->middleware('akses:produk-sku,ubah')->name('sku.syncdiskonCustomer');
            Route::get('/sku/{id_sku}/komposisi', [ProdukSkuController::class, 'komposisi'])->name('sku.komposisi');
            Route::post('/sku/{id_sku}/komposisi/sync', [KomposisiController::class, 'sync'])->middleware('akses:produk-sku,ubah')->name('sku.syncKomposisi');
            Route::delete('/sku/{id_sku}', [ProdukSkuController::class, 'destroy'])->middleware('akses:produk-sku,hapus')->name('sku.destroy');
        });
        Route::middleware('akses:varian')->group(function () {
            Route::resource('varian', VarianController::class);
            Route::post('/pilihan-varian', [PilihanVarianController::class, 'store'])->name('pilihan-varian.store');
            Route::put('/pilihan-varian/{id}', [PilihanVarianController::class, 'update'])->name('pilihan-varian.update');
            Route::delete('/pilihan-varian/{id}', [PilihanVarianController::class, 'destroy'])->name('pilihan-varian.destroy');
        });
        Route::middleware('akses:finishing')->group(function () {
            Route::resource('finishing', FinishingController::class);
            Route::post('/pilihan-finishing', [PilihanFinishingController::class, 'store'])->name('pilihan-finishing.store');
            Route::put('/pilihan-finishing/{id}', [PilihanFinishingController::class, 'update'])->name('pilihan-finishing.update');
            Route::delete('/pilihan-finishing/{id}', [PilihanFinishingController::class, 'destroy'])->name('pilihan-finishing.destroy');
        });
        Route::resource('bahan-baku', BahanBakuController::class)->middleware('akses:bahan-baku');
        Route::resource('pembelian-bahan', PembelianBahanController::class)->middleware('akses:pembelian-bahan');
        Route::resource('voucher', VoucherController::class)->middleware('akses:voucher');
        Route::middleware('akses:pesan')->group(function () {
            Route::get('pesan', [PesanController::class, 'index'])->name('pesan.index');
            Route::post('pesan', [PesanController::class, 'store'])->name('pesan.store');
            Route::get('pesan/{id}/detail', [PesanController::class, 'detail'])->name('pesan.detail');
            Route::put('pesan/{id}/pembayaran', [PembayaranController::class, 'store'])->name('pesan.updatePembayaran');
            Route::put('/pesan/{id_pesan}/resi', [PesanController::class, 'updateResi'])->middleware('akses:pesan,ubah')->name('pesan.updateResi');
            Route::get('/pesan/{id_pesan}/cetak-label', [PesanController::class, 'cetakLabel'])->name('pesan.cetakLabel');
            Route::get('/pesan/{id_pesan}/cetak-label', [PesanController::class, 'cetakLabel'])->name('pesan.cetakLabel');
            Route::get('/pesan/{id_pesan}/cetak-nota', [PesanController::class, 'cetakNota'])->name('pesan.cetakNota');
            Route::get('/pesan/item/{id}/cetak-label', [PesanController::class, 'cetakLabelItem'])->name('pesan.cetakLabelItem');
            Route::put('/pesan/{id_pesan}/update-alamat', [PesanController::class, 'updateAlamat'])->middleware('akses:pesan,ubah')->name('pesan.updateAlamat');
            Route::post('/pesan/add-item/store', [PesanController::class, 'addItem'])->middleware('akses:pesan,tambah')->name('pesan.addItem');
            Route::put('/pesan/update-item/{id}', [PesanController::class, 'updateItem'])->middleware('akses:pesan,ubah')->name('pesan.updateItem');
            Route::delete('/pesan/delete-item/{id}', [PesanController::class, 'deleteItem'])->middleware('akses:pesan,hapus')->name('pesan.deleteItem');
            Route::get('/pesan/pos-kasir', [PesanController::class, 'posKasir'])->middleware('akses:pesan,tambah')->name('pesan.pos-kasir');
        });
        Route::middleware('akses:pembayaran')->group(function () {
            Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
            Route::get('pembayaran/{id}/detail', [PembayaranController::class, 'detail'])->name('pembayaran.detail');
        });
        Route::resource('vendor', VendorController::class)->middleware('akses:vendor');
        Route::resource('tagihan-vendor', TagihanVendorController::class)->middleware('akses:tagihan-vendor');
        Route::get('/search', [GlobalSearchController::class, 'index'])->name('global.search');
        Route::middleware('akses:hak-akses')->group(function () {
            Route::resource('modul', ModulController::class)->middleware('akses:hak-akses');
            Route::get('/hak-akses', [ModulController::class, 'index'])->name('hak-akses.index');
            Route::get('/hak-akses/{id_modul}/edit', [HakAksesController::class, 'edit'])->name('hak-akses.edit');
            Route::post('/hak-akses/{id_modul}/sync', [HakAksesController::class, 'sync'])->middleware('akses:hak-akses,ubah')->name('hak-akses.sync');
        });
        Route::get('/file-manage', [FileManageController::class, 'index'])->middleware('akses:hak-akses')->name('file-manage.index');
        Route::post('/file-manage/hapus', [FileManageController::class, 'hapusMassal'])->middleware('akses:hak-akses,hapus')->name('file-manage.hapus');
    });

});

require __DIR__.'/auth.php';
