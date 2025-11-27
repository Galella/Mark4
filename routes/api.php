<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataTablesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for DataTables
Route::prefix('datatables')->middleware(['auth'])->group(function () {
    Route::get('/users', [DataTablesController::class, 'users'])->name('api.datatables.users');
    Route::get('/outlets', [DataTablesController::class, 'outlets'])->name('api.datatables.outlets');
    Route::get('/modas', [DataTablesController::class, 'modas'])->name('api.datatables.modas');
    Route::get('/offices', [DataTablesController::class, 'offices'])->name('api.datatables.offices');
    Route::get('/outlet-types', [DataTablesController::class, 'outletTypes'])->name('api.datatables.outlet-types');
    Route::get('/daily-incomes', [DataTablesController::class, 'dailyIncomes'])->name('api.datatables.daily-incomes');
    Route::get('/income-targets', [DataTablesController::class, 'incomeTargets'])->name('api.datatables.income-targets');
});