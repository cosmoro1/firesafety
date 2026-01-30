<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\SiteAuditController;
use App\Http\Controllers\HighRiskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Artisan;

// --- GUEST ROUTES (Login/Logout) ---
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- PROTECTED ROUTES (Must be logged in) ---
Route::middleware('auth')->group(function () {

    // 1. INCIDENTS
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::put('/incidents/{id}/approve', [IncidentController::class, 'approve'])->name('incidents.approve');
    Route::put('/incidents/{id}', [IncidentController::class, 'update'])->name('incidents.update');

    // 2. SITE AUDIT
    Route::get('/site-audit', [SiteAuditController::class, 'index'])->name('site_audit.index');
    Route::post('/site-audit', [SiteAuditController::class, 'store'])->name('site_audit.store');

    // 3. DASHBOARD (Now uses the Controller to show real data)
    // Note: If you want to restrict this to Admin only, you can add a check inside the Controller.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 4. ANALYTICS (Admin Only)
    Route::get('/analytics', function () {
        if (auth()->user()->role !== 'admin') {
            return redirect('/training');
        }
        return view('analytics');
    });

    // 5. DOCUMENTS (Admin & Clerk)
    Route::get('/documents', function () {
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            return redirect('/training');
        }
        return view('documents');
    });

    // 6. HIGH RISK BARANGAYS (Admin Only)
    // Ideally, you should add a check in the HighRiskController or here, 
    // but for now, we link it directly as requested.
    Route::get('/high-risk', [HighRiskController::class, 'index'])->name('high_risk.index');

    // 7. TRAINING (Everyone)
    Route::get('/training', function () {
        return view('training');
    });

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    Route::put('/incidents/{id}/status', [IncidentController::class, 'updateStatus'])->name('incidents.status');

});


Route::middleware(['auth'])->group(function () {
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
});

Route::post('/site-audit/import', [SiteAuditController::class, 'import'])->name('site_audit.import');


Route::post('/incidents/import', [IncidentController::class, 'import'])->name('incidents.import');

Route::get('/reset-database', function () {
    // This command wipes your database clean and runs your seeders
    Artisan::call('migrate:fresh --seed');

    return 'Database has been reset and users created! You can login now.';
});