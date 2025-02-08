<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PenagihController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\PelangganController;

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

     // SIDEBAR MENU DATA PELANGGAN
     Route::middleware(['auth', 'role:member,teknisi'])->prefix('pelanggan')->group(function () {
        Route::prefix('/')->controller(PelangganController::class)->group(function(){
            Route::get('/', 'index')->name('pelanggan');
            
            Route::get('/formulir', 'formulir')->name('formulir');
            Route::post('/formulir/add', 'addPelanggan')->name('addPelanggan');
            Route::get('/{id}', [PelangganController::class, 'showPelanggan'])->name('pelanggan.show');
            Route::get('/{id}/get-bandwidth', [PelangganController::class, 'getBandwidth'])->name('getBandwidth');


            // api.php
            Route::get('{id}/api/traffic-data', [PelangganController::class, 'getTrafficData'])->name('traffic.data');
        });

    });



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
            Route::get('/get-mikrotik-profiles', 'getMikrotikProfiles')->name('getMikrotikProfiles');
            Route::post('/tambahpaket', 'tambahpaket')->name('tambahpaket');
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
