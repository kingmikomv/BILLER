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

// Auth routes with email verification
Auth::routes(['verify' => true]);

// Route to home page after login
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Middleware for SUPERADMIN, requires authentication and superadmin role
Route::middleware(['auth', 'role:superadmin'])->group(function() {
    Route::get('/admin/manage-users', [AdminController::class, 'manageUsers']);
 
    // Add other routes for SUPERADMIN
});

// Middleware for MEMBER, requires authentication and member role
Route::middleware(['auth', 'role:member'])->group(function() {
    Route::get('/home/crot', function() {
        return 'crot';
    });
    // Add routes for MEMBER
});

// Middleware for TEKNISI, requires authentication and teknisi role
Route::middleware(['auth', 'role:teknisi'])->group(function() {
    // Add routes for TEKNISI
});

// Middleware for PENAGIH, requires authentication and penagih role
Route::middleware(['auth', 'role:penagih'])->group(function() {
    // Add routes for PENAGIH
});
