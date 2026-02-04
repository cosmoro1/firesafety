<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\SiteAuditController;
use App\Http\Controllers\HighRiskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| GUEST ROUTES (Login/Logout)
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Must be logged in)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. INCIDENTS
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::put('/incidents/{id}/approve', [IncidentController::class, 'approve'])->name('incidents.approve');
    Route::put('/incidents/{id}', [IncidentController::class, 'update'])->name('incidents.update');
    Route::put('/incidents/{id}/status', [IncidentController::class, 'updateStatus'])->name('incidents.status');
    Route::post('/incidents/import', [IncidentController::class, 'import'])->name('incidents.import');

    // 3. SITE AUDIT
    Route::get('/site-audit', [SiteAuditController::class, 'index'])->name('site_audit.index');
    Route::post('/site-audit', [SiteAuditController::class, 'store'])->name('site_audit.store');
    Route::post('/site-audit/import', [SiteAuditController::class, 'import'])->name('site_audit.import');

    // 4. ANALYTICS
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // 5. DOCUMENTS
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');

    // 6. HIGH RISK BARANGAYS
    Route::get('/high-risk', [HighRiskController::class, 'index'])->name('high_risk.index');

    // 7. TRAINING (Restricted to Admin & Clerk)
    Route::get('/training', function () {
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized action.');
        }
        return view('training');
    });

    // 8. USER MANAGEMENT
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

});

/*
|--------------------------------------------------------------------------
| SYSTEM UTILITIES
|--------------------------------------------------------------------------
*/
Route::get('/reset-database', function () { // <--- FIXED: Changed RRoute to Route
    Artisan::call('migrate:fresh --seed --force');
    return 'Database reset successfully! Users created. You can now login.';
});