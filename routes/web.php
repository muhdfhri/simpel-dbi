<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Desa\LaporanController as DesaLaporanController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| SIMPEL DBI — Web Routes (3 Role Architecture)
|--------------------------------------------------------------------------
*/

// Halaman utama (mengarahkan ke Login atau Dashboard sesuai auth status)
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return match ($user->role->value ?? $user->role) {
            'desa' => redirect()->route('desa.dashboard'),
            'pimpasa' => redirect()->route('pimpasa.dashboard'),
            'kanwil' => redirect()->route('kanwil.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest Auth Routes (Hanya Pengguna Belum Login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/portal-dbi', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/portal-dbi', [AuthenticatedSessionController::class, 'store']);

    // Password Reset 2FA OTP Flow
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'showForgotForm'])->name('forgot-password.show');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'sendOtp'])->name('forgot-password.send');

    Route::get('/verify-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'showVerifyForm'])->name('verify-otp.show');
    Route::post('/verify-otp', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'verifyOtp'])->name('verify-otp.verify');

    Route::get('/reset-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'showResetForm'])->name('reset-password.show');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetOtpController::class, 'resetPassword'])->name('reset-password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Harus Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Universal Notifications Routes (3-Role Architecture)
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    /*
    |--------------------------------------------------------------------------
    | Role 1: Perangkat Desa (`role: desa`)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:desa')->prefix('desa')->name('desa.')->group(function () {
        Route::get('/dashboard', \App\Http\Controllers\Desa\DesaDashboardController::class)->name('dashboard');
        
        // Resource Laporan Desa & Fitur Export (PDF & Excel)
        Route::get('/laporan', [DesaLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/create', [DesaLaporanController::class, 'create'])->name('laporan.create');
        Route::get('/laporan/export-excel', [DesaLaporanController::class, 'exportExcel'])->name('laporan.export-excel');
        Route::get('/laporan/export-pdf', [DesaLaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
        Route::post('/laporan', [DesaLaporanController::class, 'store'])->name('laporan.store');
        Route::get('/laporan/{laporan}', [DesaLaporanController::class, 'show'])->name('laporan.show');
        Route::get('/laporan/{laporan}/export-pdf', [DesaLaporanController::class, 'exportDetailPdf'])->name('laporan.export-detail-pdf');
        Route::get('/laporan/{laporan}/edit', [DesaLaporanController::class, 'edit'])->name('laporan.edit');
        Route::post('/laporan/{laporan}/update', [DesaLaporanController::class, 'update'])->name('laporan.update');

        // Status & Riwayat Tiket (Rekapitulasi & Audit Trail)
        Route::get('/riwayat', [\App\Http\Controllers\Desa\RiwayatController::class, 'index'])->name('riwayat.index');

        // Manual Book / Panduan Penggunaan Perangkat Desa
        Route::get('/panduan', [\App\Http\Controllers\Desa\PanduanController::class, 'index'])->name('panduan.index');

        // Pengaturan Akun Perangkat Desa
        Route::get('/settings', [\App\Http\Controllers\Desa\DesaSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/profile', [\App\Http\Controllers\Desa\DesaSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::put('/settings/password', [\App\Http\Controllers\Desa\DesaSettingsController::class, 'updatePassword'])->name('settings.password.update');
        Route::put('/settings/notifications', [\App\Http\Controllers\Desa\DesaSettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    });

    // Universal Redirect Routes
    Route::get('/manual-book', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if ($user && $user->role === 'kanwil') {
            return redirect()->route('kanwil.panduan.index');
        }
        if ($user && $user->role === 'pimpasa') {
            return redirect()->route('pimpasa.panduan.index');
        }
        return redirect()->route('desa.panduan.index');
    })->name('manual-book');
    Route::get('/settings', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if ($user && $user->role === 'pimpasa') {
            return redirect()->route('pimpasa.settings.index');
        }
        if ($user && $user->role === 'kanwil') {
            return redirect()->route('kanwil.settings.index');
        }
        return redirect()->route('desa.settings.index');
    })->name('settings');

    /*
    |--------------------------------------------------------------------------
    | Role 2: Petugas PIMPASA / UPT Imigrasi (`role: pimpasa`)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:pimpasa')->prefix('pimpasa')->name('pimpasa.')->group(function () {
        Route::get('/dashboard', \App\Http\Controllers\Pimpasa\DashboardController::class)->name('dashboard');

        // Menu 1: Worklist Verifikasi PIMPASA
        Route::get('/verifikasi', [\App\Http\Controllers\Pimpasa\VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/export-excel', [\App\Http\Controllers\Pimpasa\VerifikasiController::class, 'exportExcel'])->name('verifikasi.export-excel');
        Route::get('/verifikasi/export-pdf', [\App\Http\Controllers\Pimpasa\VerifikasiController::class, 'exportPdf'])->name('verifikasi.export-pdf');
        Route::get('/verifikasi/{laporan}', [\App\Http\Controllers\Pimpasa\VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{laporan}', [\App\Http\Controllers\Pimpasa\VerifikasiController::class, 'store'])->name('verifikasi.store');

        // Menu 2: Disposisi & Tindak Lanjut UPT
        Route::get('/tindak-lanjut', \App\Http\Controllers\Pimpasa\TindakLanjutIndexController::class)->name('tindaklanjut.index');
        Route::get('/tindak-lanjut/{laporan}', [\App\Http\Controllers\Pimpasa\TindakLanjutController::class, 'form'])->name('tindaklanjut.form');
        Route::post('/tindak-lanjut/{laporan}', [\App\Http\Controllers\Pimpasa\TindakLanjutController::class, 'store'])->name('tindaklanjut.store');

        // Menu 3: Kegiatan Pembinaan Desa
        Route::get('/kegiatan', [\App\Http\Controllers\Pimpasa\KegiatanPembinaanController::class, 'index'])->name('kegiatan.index');
        Route::post('/kegiatan', [\App\Http\Controllers\Pimpasa\KegiatanPembinaanController::class, 'store'])->name('kegiatan.store');
        Route::put('/kegiatan/{kegiatan}', [\App\Http\Controllers\Pimpasa\KegiatanPembinaanController::class, 'update'])->name('kegiatan.update');
        Route::delete('/kegiatan/{kegiatan}', [\App\Http\Controllers\Pimpasa\KegiatanPembinaanController::class, 'destroy'])->name('kegiatan.destroy');
        Route::get('/kegiatan/export-pdf', [\App\Http\Controllers\Pimpasa\KegiatanPembinaanController::class, 'exportPdf'])->name('kegiatan.export-pdf');
        Route::get('/kegiatan/export-excel', [\App\Http\Controllers\Pimpasa\KegiatanPembinaanController::class, 'exportExcel'])->name('kegiatan.export-excel');

        // Menu 4: Daftar Desa Binaan
        Route::get('/desa-binaan', [\App\Http\Controllers\Pimpasa\DesaBinaanController::class, 'index'])->name('desabinaan.index');
        Route::get('/desa-binaan/export-excel', [\App\Http\Controllers\Pimpasa\DesaBinaanController::class, 'exportExcel'])->name('desabinaan.export-excel');
        Route::get('/desa-binaan/export-pdf', [\App\Http\Controllers\Pimpasa\DesaBinaanController::class, 'exportPdf'])->name('desabinaan.export-pdf');

        // Menu 5: Rekapitulasi Satker UPT
        Route::get('/rekapitulasi', [\App\Http\Controllers\Pimpasa\RekapitulasiSatkerController::class, 'index'])->name('rekapitulasi.index');
        Route::get('/rekapitulasi/export-excel', [\App\Http\Controllers\Pimpasa\RekapitulasiSatkerController::class, 'exportExcel'])->name('rekapitulasi.export-excel');
        // Menu 6: Manual Book PIMPASA
        Route::get('/panduan', [\App\Http\Controllers\Pimpasa\PanduanController::class, 'index'])->name('panduan.index');

        // Menu 7: Pengaturan Akun PIMPASA
        Route::get('/settings', [\App\Http\Controllers\Pimpasa\PimpasaSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/profile', [\App\Http\Controllers\Pimpasa\PimpasaSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::put('/settings/password', [\App\Http\Controllers\Pimpasa\PimpasaSettingsController::class, 'updatePassword'])->name('settings.password.update');
        Route::put('/settings/notifications', [\App\Http\Controllers\Pimpasa\PimpasaSettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Role 3: Kantor Wilayah Ditjen Imigrasi Sumut / Super Admin (`role: kanwil`)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:kanwil')->prefix('kanwil')->name('kanwil.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Kanwil\DashboardController::class, 'index'])->name('dashboard');

        // Menu 1: Master Data System (171 Desa, 53 PIMPASA, 10 UPT)
        Route::get('/master-data', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'index'])->name('masterdata.index');
        
        // CRUD Desa Binaan
        Route::post('/master-data/desa', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'storeDesa'])->name('masterdata.desa.store');
        Route::put('/master-data/desa/{desa}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'updateDesa'])->name('masterdata.desa.update');
        Route::delete('/master-data/desa/{desa}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'destroyDesa'])->name('masterdata.desa.destroy');

        // CRUD PIMPASA
        Route::post('/master-data/pimpasa', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'storePimpasa'])->name('masterdata.pimpasa.store');
        Route::put('/master-data/pimpasa/{user}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'updatePimpasa'])->name('masterdata.pimpasa.update');
        Route::delete('/master-data/pimpasa/{user}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'destroyPimpasa'])->name('masterdata.pimpasa.destroy');

        // CRUD UPT
        Route::post('/master-data/upt', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'storeUpt'])->name('masterdata.upt.store');
        Route::put('/master-data/upt/{upt}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'updateUpt'])->name('masterdata.upt.update');
        Route::delete('/master-data/upt/{upt}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'destroyUpt'])->name('masterdata.upt.destroy');

        // CRUD User Perangkat Desa
        Route::post('/master-data/user-desa', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'storeDesaUser'])->name('masterdata.user-desa.store');
        Route::put('/master-data/user-desa/{user}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'updateDesaUser'])->name('masterdata.user-desa.update');
        Route::delete('/master-data/user-desa/{user}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'destroyDesaUser'])->name('masterdata.user-desa.destroy');

        // CRUD Administrator Kanwil
        Route::post('/master-data/user-kanwil', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'storeKanwilUser'])->name('masterdata.user-kanwil.store');
        Route::put('/master-data/user-kanwil/{user}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'updateKanwilUser'])->name('masterdata.user-kanwil.update');
        Route::delete('/master-data/user-kanwil/{user}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'destroyKanwilUser'])->name('masterdata.user-kanwil.destroy');

        // Toggle Matriks Hak Akses / Permission Control
        Route::post('/master-data/permissions/toggle', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'togglePermission'])->name('masterdata.permissions.toggle');

        // CRUD Kategori Laporan Master Data
        Route::post('/master-data/kategori', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'storeKategori'])->name('masterdata.kategori.store');
        Route::put('/master-data/kategori/{kategori}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'updateKategori'])->name('masterdata.kategori.update');
        Route::post('/master-data/kategori/{kategori}/toggle', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'toggleKategoriStatus'])->name('masterdata.kategori.toggle');
        Route::delete('/master-data/kategori/{kategori}', [\App\Http\Controllers\Kanwil\MasterDataController::class, 'destroyKategori'])->name('masterdata.kategori.destroy');

        // Menu 3: Peta Geospasial Sebaran Desa Binaan
        Route::get('/peta', [\App\Http\Controllers\Kanwil\PetaController::class, 'index'])->name('peta.index');

        // Menu 4: Executive Monitoring & SLA Command Center (3 Dedicated Operational Tools)
        Route::get('/monitoring', fn () => redirect()->route('kanwil.monitoring.sla-control'))->name('monitoring.index');
        Route::get('/monitoring/sla-control', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'slaControl'])->name('monitoring.sla-control');
        Route::post('/monitoring/tegur-sla/{laporan}', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'tegurSla'])->name('monitoring.tegur-sla');
        Route::get('/monitoring/sla-control/export-excel', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'exportSlaExcel'])->name('monitoring.sla-control.export-excel');
        Route::get('/monitoring/sla-control/export-pdf', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'exportSlaPdf'])->name('monitoring.sla-control.export-pdf');
        Route::get('/monitoring/upt-scorecard', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'uptScorecard'])->name('monitoring.upt-scorecard');
        Route::get('/monitoring/upt-scorecard/export-excel', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'exportScorecardExcel'])->name('monitoring.upt-scorecard.export-excel');
        Route::get('/monitoring/upt-scorecard/export-pdf', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'exportScorecardPdf'])->name('monitoring.upt-scorecard.export-pdf');
        Route::get('/monitoring/kegiatan-pembinaan', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'kegiatanPembinaan'])->name('monitoring.kegiatan-pembinaan');
        Route::get('/monitoring/kegiatan-pembinaan/export-excel', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'exportKegiatanExcel'])->name('monitoring.kegiatan-pembinaan.export-excel');
        Route::get('/monitoring/kegiatan-pembinaan/export-pdf', [\App\Http\Controllers\Kanwil\MonitoringController::class, 'exportKegiatanPdf'])->name('monitoring.kegiatan-pembinaan.export-pdf');

        // Menu 5: Panduan / Manual Book Kanwil
        Route::get('/panduan', fn () => Inertia::render('Kanwil/Panduan/Index'))->name('panduan.index');

        // Menu 6: Pengaturan Akun Kanwil
        Route::get('/settings', [\App\Http\Controllers\Kanwil\KanwilSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/profile', [\App\Http\Controllers\Kanwil\KanwilSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::put('/settings/password', [\App\Http\Controllers\Kanwil\KanwilSettingsController::class, 'updatePassword'])->name('settings.password.update');
        Route::put('/settings/notifications', [\App\Http\Controllers\Kanwil\KanwilSettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    });
});

// Global Fallback 404 Route (Menangkap seluruh URL yang tidak terdaftar di sistem SIMPEL DBI)
Route::fallback(function () {
    return Inertia::render('Errors/404')->toResponse(request())->setStatusCode(404);
});
