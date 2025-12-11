<?php

use App\Http\Controllers\ApiMonitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Monitoring Routes
|--------------------------------------------------------------------------
|
| Routes for API monitoring dashboard and analytics
|
*/

Route::prefix('api-monitor')->middleware(['web', 'auth'])->group(function () {
    
    // Dashboard
    Route::get('/', [ApiMonitorController::class, 'index'])->name('api-monitor.dashboard');
    
    // Request detail
    Route::get('/request/{requestId}', [ApiMonitorController::class, 'show'])->name('api-monitor.request');
    
    // Logs viewer
    Route::get('/logs', [ApiMonitorController::class, 'logs'])->name('api-monitor.logs');
    
    // Performance metrics
    Route::get('/metrics', [ApiMonitorController::class, 'metrics'])->name('api-monitor.metrics');
    
    // Export logs
    Route::get('/export', [ApiMonitorController::class, 'export'])->name('api-monitor.export');
    
    // API endpoints for real-time data
    Route::prefix('api')->group(function () {
        Route::get('/realtime', [ApiMonitorController::class, 'realtime'])->name('api-monitor.api.realtime');
        Route::get('/health', [ApiMonitorController::class, 'health'])->name('api-monitor.api.health');
    });
});