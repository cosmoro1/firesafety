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
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\SettingsController;

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
    Route::get('/incidents/{incident}/download', [IncidentController::class, 'download'])->name('incidents.download');
    
    // NEW: View Full Incident Report
    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');

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

    // 7. TRAINING
    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
    Route::post('/training', [TrainingController::class, 'store'])->name('training.store');
    Route::post('/training/{training}/email', [TrainingController::class, 'sendEmail'])->name('training.email');
    Route::put('/training/{training}', [TrainingController::class, 'update'])->name('training.update');
    Route::delete('/training/{training}', [TrainingController::class, 'destroy'])->name('training.destroy');

    // 8. USER MANAGEMENT (Admin controls)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // 9. SETTINGS (Self-Service Profile & Password)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');

});

/*
|--------------------------------------------------------------------------
| SYSTEM UTILITIES
|--------------------------------------------------------------------------
*/
Route::get('/reset-database', function () {
    Artisan::call('migrate:fresh --seed --force');
    return 'Database reset successfully! Users created. You can now login.';
});