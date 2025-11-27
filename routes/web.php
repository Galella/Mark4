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
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export')->middleware(['auth']);

    // User management routes
    Route::resource('users', UserController::class)->middleware(['can:viewAny,App\Models\User']);

    // Office management export route (defined before resource to avoid conflicts)
    Route::get('/offices/export', [OfficeController::class, 'export'])->name('offices.export')->middleware(['auth']);

    // Office management routes
    Route::resource('offices', OfficeController::class)->middleware(['can:viewAny,App\Models\Office']);

    // Outlet management import/export routes (defined before resource to avoid conflicts)
    Route::get('/outlets/import', [OutletController::class, 'showImportForm'])->name('outlets.import.form')->middleware(['auth']);
    Route::post('/outlets/import', [OutletController::class, 'import'])->name('outlets.import')->middleware(['auth']);
    Route::get('/outlets/export', [OutletController::class, 'export'])->name('outlets.export')->middleware(['auth']);

    // Outlet management routes
    Route::resource('outlets', OutletController::class)->middleware(['can:viewAny,App\Models\Outlet']);

    // Outlet type management export route (defined before resource to avoid conflicts)
    Route::get('/outlet-types/export', [OutletTypeController::class, 'export'])->name('outlet-types.export')->middleware(['auth']);

    // Outlet type management routes
    Route::resource('outlet-types', OutletTypeController::class);

    // Daily income routes - only for admin outlet
    Route::resource('daily-incomes', DailyIncomeController::class)->middleware(['auth']);

    // Daily income import routes (separate section for bulk imports)
    Route::get('/import/daily-income', [DailyIncomeController::class, 'showImportForm'])->name('import.daily-income.form')->middleware(['auth']);
    Route::post('/import/daily-income', [DailyIncomeController::class, 'import'])->name('import.daily-income.import')->middleware(['auth']);
    Route::get('/import/daily-income/template', [DailyIncomeController::class, 'downloadImportTemplate'])->name('import.daily-income.template')->middleware(['auth']);

    // Moda management routes - only for super admin, admin wilayah, and admin area
    Route::resource('modas', \App\Http\Controllers\ModaController::class)->middleware(['auth']);

    // Activity log routes
    Route::resource('activity-logs', ActivityLogController::class)->middleware(['can:viewAny,App\Models\ActivityLog']);

    // Daily income report routes
    Route::get('/reports/daily-income', [DailyIncomeReportController::class, 'index'])->name('reports.daily-income.index')->middleware(['auth']);
    Route::get('/reports/daily-income/summary', [DailyIncomeReportController::class, 'summary'])->name('reports.daily-income.summary')->middleware(['auth']);
    Route::get('/reports/daily-income/export', [DailyIncomeReportController::class, 'exportExcel'])->name('reports.daily-income.export')->middleware(['auth']);
    Route::get('/reports/daily-income/export-summary', [DailyIncomeReportController::class, 'exportSummaryExcel'])->name('reports.daily-income.export-summary')->middleware(['auth']);

    // Target vs Realization report routes
    Route::get('/reports/target-realization', [\App\Http\Controllers\Reports\TargetRealizationReportController::class, 'index'])->name('reports.target-realization.index')->middleware(['auth']);
    Route::get('/reports/target-realization/export', [\App\Http\Controllers\Reports\TargetRealizationReportController::class, 'exportExcel'])->name('reports.target-realization.export')->middleware(['auth']);

    // Dashboard AJAX routes for income stats
    Route::get('/dashboard/income-stats', [DashboardController::class, 'getIncomeStatsAjax'])->name('dashboard.income-stats')->middleware(['auth']);
    Route::get('/dashboard/income-trend', [DashboardController::class, 'getIncomeTrendAjax'])->name('dashboard.income-trend')->middleware(['auth']);
    Route::get('/dashboard/income-by-moda', [DashboardController::class, 'getIncomeByModaAjax'])->name('dashboard.income-by-moda')->middleware(['auth']);
    Route::get('/dashboard/income-by-outlet', [DashboardController::class, 'getIncomeByOutletAjax'])->name('dashboard.income-by-outlet')->middleware(['auth']);
    Route::get('/dashboard/income-by-moda-per-month', [DashboardController::class, 'getIncomeByModaPerMonthAjax'])->name('dashboard.income-by-moda-per-month')->middleware(['auth']);
    Route::get('/dashboard/income-by-outlet-per-month', [DashboardController::class, 'getIncomeByOutletPerMonthAjax'])->name('dashboard.income-by-outlet-per-month')->middleware(['auth']);

    // Temporary route for checking users (without auth middleware)
    Route::get('/test-users', [TestController::class, 'showUsers']);
    
    // Income targets import routes (defined before resource to avoid conflicts)
    Route::get('/income-targets/import', [\App\Http\Controllers\IncomeTargetController::class, 'showImportForm'])
         ->name('income-targets.import.form')
         ->middleware(['auth']);
    Route::post('/income-targets/import', [\App\Http\Controllers\IncomeTargetController::class, 'import'])
         ->name('income-targets.import')
         ->middleware(['auth']);

    // Income targets resource routes
    Route::resource('income-targets', \App\Http\Controllers\IncomeTargetController::class)
        ->middleware(['auth']);
    
    // Todo routes
    Route::apiResource('todos', \App\Http\Controllers\TodoController::class)->middleware(['auth']);
    Route::post('/todos/{todo}/toggle', [\App\Http\Controllers\TodoController::class, 'toggle'])->name('todos.toggle')->middleware(['auth']);
});

// Route sederhana untuk menampilkan user (tanpa auth)
Route::get('/show-users', function() {
    $users = App\Models\User::with(['office', 'outlet'])->get();

    echo "<h2>Daftar User</h2>\n";
    foreach($users as $user) {
        echo "<p><strong>Nama:</strong> {$user->name}<br>";
        echo "<strong>Email:</strong> {$user->email}<br>";
        echo "<strong>Role:</strong> {$user->role}<br>";
        if($user->office) {
            echo "<strong>Office:</strong> {$user->office->name} ({$user->office->type})<br>";
        }
        if($user->outlet) {
            echo "<strong>Outlet:</strong> {$user->outlet->name}<br>";
        }
        echo "</p><hr>\n";
    }

    echo "<h2>Daftar Office</h2>\n";
    $offices = App\Models\Office::all();
    foreach($offices as $office) {
        echo "<p><strong>Nama:</strong> {$office->name}<br>";
        echo "<strong>Code:</strong> {$office->code}<br>";
        echo "<strong>Tipe:</strong> {$office->type}<br>";
        if($office->parent) {
            echo "<strong>Parent:</strong> {$office->parent->name}<br>";
        }
        echo "</p><hr>\n";
    }
});

// Temporary test routes for debugging import functionality
Route::get('/test-import-method', function () {
    $controller = new \App\Http\Controllers\IncomeTargetController(app(\App\Services\ActivityLogService::class));
    return $controller->showImportForm();
})->middleware(['auth']);

Route::get('/test-import-view', function () {
    return view('income-targets.import');
})->middleware(['auth']);

Route::get('/test-route', function () {
    return 'Route is working';
});

// Test route bypassing authorization
Route::get('/test-bypass-import', function () {
    return view('income-targets.import');
})->middleware(['auth']);

// Test authorization for IncomeTarget creation
Route::get('/test-authorization', function () {
    $user = auth()->user();
    $canCreate = $user->can('create', \App\Models\IncomeTarget::class);
    return [
        'user_can_create_income_target' => $canCreate,
        'user_is_admin_outlet' => $user->isAdminOutlet(),
    ];
})->middleware(['auth']);

// Test direct controller method
Route::get('/test-controller-method', function () {
    // This will test the method without complex dependencies if possible
    $user = auth()->user();
    $canCreate = $user->can('create', \App\Models\IncomeTarget::class);

    if (!$canCreate) {
        abort(403, 'Unauthorized - cannot create income targets');
    }

    return view('income-targets.import');
})->middleware(['auth']);

// Another test route with direct controller method call
Route::get('/direct-import-call', function () {
    $activityLogService = app(\App\Services\ActivityLogService::class);
    $controller = new \App\Http\Controllers\IncomeTargetController($activityLogService);
    return $controller->showImportForm();
})->middleware(['auth']);

Route::get('/debug-user', function () {
    $user = auth()->user();
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isAdminWilayah' => $user->isAdminWilayah(),
            'isAdminArea' => $user->isAdminArea(),
            'isAdminOutlet' => $user->isAdminOutlet(),
        ];
    } else {
        return 'No authenticated user';
    }
})->middleware(['auth']);