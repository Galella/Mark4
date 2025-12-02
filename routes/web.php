<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OutletTypeController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DailyIncomeController;
use App\Http\Controllers\ModaController;
use App\Http\Controllers\DailyIncomeReportController;
use App\Http\Controllers\IncomeTargetController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\OutletPerformanceController;
use App\Http\Controllers\Reports\TargetRealizationReportController;

// Guest routes - Make login page as home page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('home');

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login.limit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Registration routes (only for creating admin users)
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register.show');
Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('throttle:register.limit');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User management export route (defined before resource to avoid conflicts)
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // User management routes
    Route::resource('users', UserController::class)->middleware(['can:viewAny,App\Models\User']);

    // Office management export route (defined before resource to avoid conflicts)
    Route::get('/offices/export', [OfficeController::class, 'export'])->name('offices.export');

    // Office management routes
    Route::resource('offices', OfficeController::class)->middleware(['can:viewAny,App\Models\Office']);

    // Outlet management import/export routes (defined before resource to avoid conflicts)
    Route::get('/outlets/import', [OutletController::class, 'showImportForm'])->name('outlets.import.form');
    Route::post('/outlets/import', [OutletController::class, 'import'])->name('outlets.import');
    Route::get('/outlets/export', [OutletController::class, 'export'])->name('outlets.export');
    Route::get('/outlets/import-template', [OutletController::class, 'downloadImportTemplate'])->name('outlets.import.template');

    // Outlet management routes
    Route::resource('outlets', OutletController::class)->middleware(['can:viewAny,App\Models\Outlet']);

    // Outlet type management export route (defined before resource to avoid conflicts)
    Route::get('/outlet-types/export', [OutletTypeController::class, 'export'])->name('outlet-types.export');

    // Outlet type management routes
    Route::resource('outlet-types', OutletTypeController::class);

    // Daily income routes - only for admin outlet
    Route::resource('daily-incomes', DailyIncomeController::class)->middleware(['role:admin_outlet']);

    // Daily income import routes (separate section for bulk imports)
    Route::get('/import/daily-income', [DailyIncomeController::class, 'showImportForm'])->name('import.daily-income.form');
    Route::post('/import/daily-income', [DailyIncomeController::class, 'import'])->name('import.daily-income.import');
    Route::get('/import/daily-income/template', [DailyIncomeController::class, 'downloadImportTemplate'])->name('import.daily-income.template');
    Route::get('/import/daily-income/progress', [DailyIncomeController::class, 'getImportProgress'])->name('import.daily-income.progress');

    // Moda management routes - only for super admin, admin wilayah, and admin area
    Route::resource('modas', ModaController::class);

    // Activity log routes
    Route::resource('activity-logs', ActivityLogController::class)->middleware(['can:viewAny,App\Models\ActivityLog']);

    // Daily income report routes
    Route::get('/reports/daily-income', [DailyIncomeReportController::class, 'index'])->name('reports.daily-income.index');
    Route::get('/reports/daily-income/summary', [DailyIncomeReportController::class, 'summary'])->name('reports.daily-income.summary');
    Route::get('/reports/daily-income/export', [DailyIncomeReportController::class, 'exportExcel'])->name('reports.daily-income.export');
    Route::get('/reports/daily-income/export-summary', [DailyIncomeReportController::class, 'exportSummaryExcel'])->name('reports.daily-income.export-summary');

    // Target vs Realization report routes
    Route::get('/reports/target-realization', [TargetRealizationReportController::class, 'index'])->name('reports.target-realization.index');
    Route::get('/reports/target-realization/export', [TargetRealizationReportController::class, 'exportExcel'])->name('reports.target-realization.export');

    // Dashboard AJAX routes for income stats
    Route::get('/dashboard/income-stats', [DashboardController::class, 'getIncomeStatsAjax'])->name('dashboard.income-stats');
    Route::get('/dashboard/income-trend', [DashboardController::class, 'getIncomeTrendAjax'])->name('dashboard.income-trend');
    Route::get('/dashboard/income-by-moda', [DashboardController::class, 'getIncomeByModaAjax'])->name('dashboard.income-by-moda');
    Route::get('/dashboard/income-by-outlet', [DashboardController::class, 'getIncomeByOutletAjax'])->name('dashboard.income-by-outlet');
    Route::get('/dashboard/income-by-moda-per-month', [DashboardController::class, 'getIncomeByModaPerMonthAjax'])->name('dashboard.income-by-moda-per-month');
    Route::get('/dashboard/income-by-outlet-per-month', [DashboardController::class, 'getIncomeByOutletPerMonthAjax'])->name('dashboard.income-by-outlet-per-month');
    Route::get('/dashboard/income-by-moda-per-month-percentage', [DashboardController::class, 'getIncomeByModaPerMonthPercentageAjax'])->name('dashboard.income-by-moda-per-month-percentage');
    Route::get('/dashboard/income-per-month-for-outlet', [DashboardController::class, 'getIncomePerMonthForOutletAjax'])->name('dashboard.income-per-month-for-outlet');

        
    // Income targets import routes (defined before resource to avoid conflicts)
    Route::get('/income-targets/import', [IncomeTargetController::class, 'showImportForm'])
         ->name('income-targets.import.form');
    Route::post('/income-targets/import', [IncomeTargetController::class, 'import'])
         ->name('income-targets.import');

    // Income targets resource routes
    Route::resource('income-targets', IncomeTargetController::class);

    // Todo routes
    Route::apiResource('todos', TodoController::class);
    Route::post('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');

    // Outlet performance routes - redirect to unified report
    Route::get('/outlet-performance', function () {
        return redirect()->route('reports.target-realization.index', ['view' => 'detailed']);
    })->name('outlet-performance.index');
    Route::get('/outlet-performance/dashboard', function () {
        return redirect()->route('reports.target-realization.index', ['view' => 'dashboard']);
    })->name('outlet-performance.dashboard');
});

// Redirect after authentication
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home-redirect');


