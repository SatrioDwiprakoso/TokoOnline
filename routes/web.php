<?php

use App\Http\Controllers\BerandaController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\RajaOngkirControllerV2;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('beranda');
});

Route::get('/test-gd', function () {
    dd(function_exists('imagecreatefromjpeg'));;
});

//API Google
Route::get('/auth/redirect', [CustomerController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/google/callback', [CustomerController::class, 'callback'])->name('auth.callback');
// Logout
Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');

//FrontEnd
Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('/produk/detail/{id}', [ProdukController::class, 'detail'])->name('produk.detail');
Route::get('/produk/kategori/{id}', [ProdukController::class, 'produkKategori'])->name('produk.kategori');
Route::get('/produk/all', [ProdukController::class, 'produkAll'])->name('produk.all');

//BackEnd
Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])->name('backend.beranda');
Route::get('backend/login', [LoginController::class, 'loginBackend'])->name('backend.login');
Route::post('backend/login', [LoginController::class, 'authenticateBackend'])->name('backend.login');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');

// Route::resource('backend/user', UserController::class)->middleware('auth');
Route::resource('backend/user', UserController::class, ['as' => 'backend'])->middleware('auth');
Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend'])->middleware('auth');
Route::resource('backend/produk', ProdukController::class, ['as' => 'backend'])->middleware('auth');
Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])->name('backend.foto_produk.store')->middleware('auth');
Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])->name('backend.foto_produk.destroy')->middleware('auth');
Route::get('backend/laporan/formuser', [UserController::class, 'formUser'])->name('backend.laporan.formuser')->middleware('auth');
Route::post('backend/laporan/cetakuser', [UserController::class, 'cetakUser'])->name('backend.laporan.cetakuser')->middleware('auth');
Route::get('backend/laporan/formproduk', [ProdukController::class, 'formProduk'])->name('backend.laporan.formproduk')->middleware('auth');
Route::post('backend/laporan/cetakproduk', [ProdukController::class, 'cetakProduk'])->name('backend.laporan.cetakproduk')->middleware('auth');
// Route untuk Customer
Route::resource('backend/customer', CustomerController::class, ['as' => 'backend'])->middleware('auth');
// Pesanan Backend
Route::get('backend/pesanan/proses', [OrderController::class, 'statusProses'])
    ->name('pesanan.proses')
    ->middleware('auth');

Route::get('backend/pesanan/selesai', [OrderController::class, 'statusSelesai'])
    ->name('pesanan.selesai')
    ->middleware('auth');

Route::get('backend/pesanan/detail/{id}', [OrderController::class, 'statusDetail'])
    ->name('pesanan.detail')
    ->middleware('auth');

Route::put('backend/pesanan/update/{id}', [OrderController::class, 'statusUpdate'])
    ->name('pesanan.update')
    ->middleware('auth');

// Group route untuk customer
Route::middleware('is.customer')->group(function () {
    // Route untuk menampilkan halaman akun customer
    Route::get('/customer/akun/{id}', [CustomerController::class, 'akun'])
        ->name('customer.akun');

    // Route untuk mengupdate data akun customer
    Route::put('/customer/updateakun/{id}', [CustomerController::class, 'updateAkun'])
        ->name('customer.updateakun');

        // Route untuk menambahkan produk ke keranjang
    Route::post('add-to-cart/{id}', [OrderController::class, 'addToCart'])->name('order.addToCart');
    Route::get('cart', [OrderController::class, 'viewCart'])->name('order.cart');

    // Route untuk menampilkan halaman akun customer
    Route::post('cart/update/{id}', [OrderController::class, 'updateCart'])->name('order.updateCart');
    Route::post('remove/{id}', [OrderController::class, 'removeFromCart'])->name('order.remove');
// rajaongkir 
    Route::post('select-shipping', [OrderController::class, 'selectShipping'])->name('order.selectShipping'); 
    Route::post('update-ongkir', [OrderController::class, 'updateOngkir'])->name('order.update-ongkir'); 
    // pembayaran 
    Route::get('select-payment', [OrderController::class, 'selectPayment'])->name('order.selectpayment'); 

    Route::post('order/complete', [OrderController::class, 'complete'])->name('order.complete'); 
    Route::get('history', [OrderController::class, 'orderHistory'])->name('order.history'); 
});

// cek_raja_ongkir_v2 
Route::get('/cek-ongkir', function () { 
    return view('cek-ongkir'); 
}); 
Route::get('/ongkir/get-destination', [RajaOngkirControllerV2::class, 'getDestination']); 
Route::post('/ongkir/calculate', [RajaOngkirControllerV2::class, 'calculateOngkir']);
