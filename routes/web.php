<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\PenagihController;
use App\Http\Controllers\TeknisiController;

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
    return view('welcome');
});

// Auth routes with email verification
Auth::routes(['verify' => true]);

// Route to home page after login
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Middleware for SUPERADMIN, requires authentication and superadmin role
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers']);

    // Add other routes for SUPERADMIN
});

// Middleware for MEMBER, requires authentication and member role
Route::middleware(['auth', 'role:member'])->prefix('home')->group(function () {

   

    // SIDEBAR MENU NETWORK
    Route::middleware(['auth', 'role:member,teknisi'])->prefix('network')->group(function () {
        // NETWORK ROUTER
        Route::prefix('router')->controller(MikrotikController::class)->group(function(){
            Route::get('/', 'router')->name('member.router');
            Route::post('/add', 'store')->name('member.router.tambah');
            Route::get('/cek-koneksi/{routerId}', 'cekKoneksi')->name('cek-koneksi');
        });

    });


    // SIDEBAR MENU INTERNET PLAN
    Route::middleware(['auth', 'role:member,teknisi'])->prefix('iplan')->group(function () {
        // PPPOE 
        Route::prefix('pppoe')->controller(MikrotikController::class)->group(function(){
            Route::get('/', 'pppoe')->name('member.pppoe');
            Route::get('/get-mikrotik-profiles', [MikroTikController::class, 'getMikrotikProfiles'])->name('getMikrotikProfiles');

        }); 

    });




});

// Middleware for TEKNISI, requires authentication and teknisi role
Route::middleware(['auth', 'role:teknisi'])->group(function () {
    // Add routes for TEKNISI
});

// Middleware for PENAGIH, requires authentication and penagih role
Route::middleware(['auth', 'role:penagih'])->group(function () {
    // Add routes for PENAGIH
});




// BUAT MEMBER DAN TEKNISI
Route::middleware(['auth', 'role:member,teknisi'])->prefix('home/network/router')->group(function () {
    Route::get('/cek-koneksi/{routerId}', [MikrotikController::class, 'cekKoneksi'])->name('cek-koneksi');
});
