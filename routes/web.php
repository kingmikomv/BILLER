<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
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

Auth::routes(['verify' => true]);

// Resend verification email
Route::get('/email/verify/resend', [App\Http\Controllers\Auth\VerificationController::class, 'resend'])->middleware('auth')->name('verification.send');












Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::middleware(['role:superadmin'])->group(function() {
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers']);

    // Route lainnya untuk SUPERADMIN
});

// Route untuk MEMBER
Route::middleware(['role:member'])->group(function() {
   
    // Route lainnya untuk MEMBER
});

// Route untuk TEKNISI
Route::middleware(['role:teknisi'])->group(function() {
 
    // Route lainnya untuk TEKNISI
});

// Route untuk PENAGIH
Route::middleware(['role:penagih'])->group(function() {
    // Route lainnya untuk PENAGIH
});