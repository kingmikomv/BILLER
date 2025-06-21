<?php

use App\Helpers\WhatsappHelper;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\RadiusController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OLTController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\UsahaController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PenagihController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\MidtransWebhookController;

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
// routes/api.php
//Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle']);

Route::get('/', function () {
    return redirect()->to('/login');
});

// Auth routes with email verification
Auth::routes(['verify' => true, 'register' => true]);

// Route to home page after login
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Middleware for SUPERADMIN, requires authentication and superadmin role
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/home/sumin/manage-users', [AdminController::class, 'manageUsers']);
    Route::get('/home/sumin/pelangganaqt', [AdminController::class, 'pelangganaqt'])->name('pelanggan.aqt');
    Route::get('/home/sumin/undian/daftarundian', [AdminController::class, 'daftarundian'])->name('pelanggan.daftarundian');
    Route::post('/home/sumin/undian/daftarundian/upload-foto-pemenang', [AdminController::class, 'uploadFotoPemenang'])->name('upload.foto.pemenang');

    Route::post('/home/sumin/undian/daftarundian/tambahundian', [AdminController::class, 'tambahundian'])->name('undian.tambahundian');

    Route::get('/home/sumin/undian/kocok', [AdminController::class, 'kocok'])->name('undian.kocok');
    Route::get('/home/sumin/undian/spinner', [AdminController::class, 'spinner'])->name('undian.spinner');
    Route::post('/home/sumin/undian/spinner/update-winner', [AdminController::class, 'updateWinner'])->name('update.winner');

    Route::delete('/undian/{id}', [AdminController::class, 'destroy'])->name('undian.destroy');
    Route::put('/undian/update', [AdminController::class, 'update'])->name('undian.update');



});

// Middleware for MEMBER, requires authentication and member role
Route::middleware(['auth', 'role:member,teknisi,cs'])->prefix('home')->group(function () {

    Route::middleware(['role:member'])->prefix('whatsapp')->group(function () {
        Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp');
        Route::get('/whatsapp/status', [WhatsappController::class, 'getStatus'])->name('wa.status');
    Route::get('/whatsapp/qr', [WhatsappController::class, 'getQRCode'])->name('wa.qr');
    Route::get('/whatsapp/start', [WhatsappController::class, 'startSession'])->name('wa.start');
    Route::get('/whatsapp/disconnect', [WhatsappController::class, 'disconnectSession'])->name('wa.disconnect');



        Route::get('/template/invoice', [WhatsappController::class, 'template'])->name('whatsapp.template');
        Route::post('/template/invoice', action: [WhatsappController::class, 'store'])->name('whatsapp.store');
        Route::post('/template/bulk-store', [WhatsappController::class, 'bulkStore'])->name('whatsapp.bulkStore');
        // Route::post('/send-whatsapp', [WhatsappHelper::class, 'sendMessage'])->name('send.whatsapp');
    });

    // SIDEBAR MENU DATA PELANGGAN
    Route::middleware(['role:member,teknisi,cs'])->prefix('pelanggan')->controller(PelangganController::class)->group(function () {
        Route::get('/', 'index')->name('pelanggan');
        Route::post('/cek-modem', 'cekModem')->name('cek.modem');
        Route::get('/formulir', 'formulir')->name('formulir');
        Route::post('/formulir/add', 'addPelanggan')->name('addPelanggan');
        Route::get('/{id}/show', 'showPelanggan')->name('pelanggan.show');
        Route::get('/{id}/show/get-bandwidth', 'getBandwidth')->name('getBandwidth');
        Route::get('/cek-ping/{akun}', 'cekPing')->name('cekPing');

        // API Traffic Data
        Route::get('{id}/api/traffic-data', 'getTrafficData')->name('traffic.data');

        Route::post('/restart', [PelangganController::class, 'restartUser'])->name('pelanggan.restart');
        Route::post('/kirim-tagihan', [PelangganController::class, 'kirimTagihan'])->name('pelanggan.kirimTagihan');
        Route::post('/isolir', [PelangganController::class, 'isolir'])->name('pelanggan.isolir');
        Route::post('/buka-isolir', [PelangganController::class, 'bukaIsolir'])->name('pelanggan.bukaIsolir');
        Route::post('/broadcast-wa', [PelangganController::class, 'broadcastWA'])->name('pelanggan.broadcastWA');
        Route::post('/broadcastWA', [PelangganController::class, 'broadcastWAPS'])->name('pelanggan.broadcastWAPS');

        Route::get('/riwayat-pemasangan/{tiket}', [TeknisiController::class, 'show'])->name('riwayat.pemasangan');


    });


    // SIdebar SALES member

    Route::middleware(['role:member,teknisi,cs'])->prefix('sales')->group(function () {

        Route::get('/data_sales', [SalesController::class, 'data_sales'])->name('data_sales');
        Route::get('/data_sales/{id}/acc', [SalesController::class, 'acc'])->name('acc');
        Route::post('/data_sales/{id}/acc/transfer', [SalesController::class, 'addPelanggan'])->name('transfer');
    });


    //Sidebar menu billing


    // SIDEBAR MENU NETWORK
    Route::middleware(['role:member,teknisi'])->prefix('network')->group(function () {
        // NETWORK ROUTER
        Route::prefix('router')->controller(MikrotikController::class)->group(function () {
            Route::get('/', 'router')->name('member.router');
            Route::post('/add', 'store')->name('member.router.tambah');
            Route::get('/cek-koneksi/{routerId}', 'cekKoneksi')->name('cek-koneksi');
        });
        Route::prefix('radius')->controller(RadiusController::class)->group(function () {
            Route::get('/', 'index')->name('member.radius');
            Route::delete('/vpn-users/{id}', 'hapusVpnRadius')->name('radius.hapusVpnRadius');
            Route::delete('/nas/{id}', 'hapusNasRadius')->name('radius.hapusNasRadius');

            Route::post('/tambahVpnRadius', 'tambahVpnRadius')->name('radius.tambahVpnRadius');
            Route::post('/tambahNasRadius', 'tambahNasRadius')->name('radius.tambahNasRadius');
        });
    });



    Route::middleware(['role:member,teknisi'])->prefix('hotspot')->controller(HotspotController::class)->group(function () {
        Route::get('/userHotspot', 'userHotspot')->name('hotspot.userHotspot');
        Route::post('/userHotspot/tambahVoucher', 'tambahVoucher')->name('hotspot.tambahVoucher');
        Route::get('/profileHotspot', 'profileHotspot')->name('hotspot.profileHotspot');
        Route::post('/profileHotspot/uploadProfile', 'uploadProfile')->name('hotspot.uploadProfile');

    });










    // SIDEBAR MENU INTERNET PLAN
    Route::middleware(['role:member,teknisi'])->prefix('iplan')->group(function () {
        // PPPOE
        Route::prefix('pppoe')->controller(MikrotikController::class)->group(function () {
            Route::get('/', 'pppoe')->name('member.pppoe');
            Route::get('/get-mikrotik-profiles', 'getMikrotikProfiles')->name('getMikrotikProfiles');
            Route::get('/add', 'addPppoe')->name('addPppoe');
            Route::post('/add/tambahpaket', 'tambahpaket')->name('tambahpaket');
        });
    });

    // SIDEBAR MENU OLT
    Route::middleware(['role:member,teknisi'])->prefix('olt')->group(function () {
        // EPON
        Route::prefix('olt')->controller(OLTController::class)->group(function () {
            Route::get('/epon', 'epon')->name('olt.epon');
            Route::post('/tambah-epon', 'tambah_epon')->name('tambah.olt.epon');
        });
    });

    // SIDEBAR MENU PEKERJA
    Route::middleware(['role:member'])->prefix('pekerja')->controller(MemberController::class)->group(function () {
        Route::get('/', 'pekerja')->name('pekerja');
        Route::post('/addPekerja', 'addPekerja')->name('addPekerja');
    });

    Route::middleware(['role:member'])->prefix('company')->controller(MemberController::class)->group(function () {
        Route::get('/', 'company')->name('company');
        Route::put('/profil-perusahaan/{id}/up', 'updateCompany')->name('updateCompany');

    });


    // usaha
    Route::middleware(['auth', 'role:member'])->group(function () {
        Route::get('/profil-usaha', [UsahaController::class, 'index'])->name('profil.usaha');
        Route::post('/profil/usaha/store-or-update', [UsahaController::class, 'storeOrUpdate'])
            ->name('profil.usaha.storeOrUpdate');
    });



});


// Middleware for TEKNISI, requires authentication and teknisi role
Route::middleware(['auth', 'role:teknisi'])->group(function () {
    Route::prefix('home/teknisi')->controller(TeknisiController::class)->group(function () {
        Route::get('/datapsb', 'datapsb')->name('datapsb');
        Route::get('/datapsb/yes/{tiket_id}', 'konfirmasiPemasangan')->name('pemasangan.konfirmasi');

    });
});

// Middleware for PENAGIH, requires authentication and penagih role
Route::middleware(['auth', 'role:penagih'])->group(function () {
    // Add routes for PENAGIH
});




// BUAT MEMBER DAN TEKNISI
Route::middleware(['auth', 'role:member,teknisi'])->prefix('home/network/router')->group(function () {
    Route::get('/cek-koneksi/{routerId}', [MikrotikController::class, 'cekKoneksi'])->name('cek-koneksi');
});

//BUAT MEMBER DAN PENAGIH 
Route::middleware(['auth', 'role:member,penagih'])->prefix('home/billing')->controller(BillingController::class)->group(function () {

    Route::get('/unpaid', 'unpaid')->name('unpaid');
    Route::post('/unpaid/confirm-bayar', 'confirmBayar')->name('tagihan.confirmBayar');
    Route::post('/tagihan/kirim-wa', 'kirimWhatsapp')->name('tagihan.kirimWa');
    Route::post('/tagihan/update', 'updateTagihan')->name('tagihan.update');

    Route::get('/unpaid/detail/{id}', 'showUnpaidDetail')->name('billing.unpaid.detail');
    Route::get('/unpaid/invoice/{invoice}/bayar', 'bayar')->name('invoice.bayar');


    Route::get('/billing_setting', 'billingSetting')->name('billing.setting');
    Route::post('/setting-billing', 'store')->name('setting-billing.store');
    


    Route::get('/paid', 'paid')->name('paid');
    Route::get('/paid/detail/{id}', action: 'showDetail')->name('billing.paid.detail');
    ;
    Route::get('/riwayat', 'riwayatTagihan')->name('riwayat');
    Route::get('/bil_pelanggan', 'bil_pelanggan')->name('bil_pelanggan');

    Route::delete('/bil_pelanggan/hapus-data/{id}', 'hapusData')->name('hapusData');

    Route::post('/send-whatsapp', 'kirimwa')->name('send.whatsapp');
    Route::post('/bil_pelanggan/updatePelanggan', 'updatePelanggan')->name('updatePelanggan');

    Route::get('/bcwa', 'bcwa')->name('bcwa');

    Route::post('/import-excel', 'importExcel')->name('import.excel');
    Route::get('/export-excel', 'exportExcel')->name('export.excel');


});

Route::middleware(['auth', 'role:sales'])->prefix('home/sales')->group(function () {
    Route::get('/data_psbsales', [SalesController::class, 'data_sales'])->name('data_psbsales');
    Route::get('/tambah_psb_sales', [SalesController::class, 'tambah_psb_sales'])->name('tambah_psb_sales');
    Route::post('/upload_psb_sales', [SalesController::class, 'upload_psb_sales'])->name('upload_psb_sales');

    Route::get('/data_psbsales/{id}/acc', [SalesController::class, 'acc_psb'])->name('acc_psb');

});
