<?php

use App\Http\Controllers\CommissionModelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OverrideCommissionModelController;
use App\Http\Controllers\UniLevelCommissionModelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Commission Model Routes (/commission-models) - Model 1 Weakest Link
Route::prefix('commission-models')->name('commission-models.')->group(function () {
    Route::get('/', [CommissionModelController::class, 'index'])->name('index');
    Route::get('/create', [CommissionModelController::class, 'create'])->name('create');
    Route::post('/', [CommissionModelController::class, 'store'])->name('store');
    Route::post('/calculate', [CommissionModelController::class, 'calculate'])->name('calculate');
    Route::get('/{model}', [CommissionModelController::class, 'show'])->name('show');
    Route::get('/{model}/edit', [CommissionModelController::class, 'edit'])->name('edit');
    Route::put('/{model}', [CommissionModelController::class, 'update'])->name('update');
    Route::get('/{model}/duplicate', [CommissionModelController::class, 'duplicate'])->name('duplicate');
    Route::delete('/{model}', [CommissionModelController::class, 'destroy'])->name('destroy');
});

// Model 2: Level / Generation Override Commission Model Routes (/override-models)
Route::prefix('override-models')->name('override-models.')->group(function () {
    Route::get('/create', [OverrideCommissionModelController::class, 'create'])->name('create');
    Route::post('/', [OverrideCommissionModelController::class, 'store'])->name('store');
    Route::post('/calculate', [OverrideCommissionModelController::class, 'calculate'])->name('calculate');
    Route::get('/{model}', [OverrideCommissionModelController::class, 'show'])->name('show');
    Route::get('/{model}/edit', [OverrideCommissionModelController::class, 'edit'])->name('edit');
    Route::put('/{model}', [OverrideCommissionModelController::class, 'update'])->name('update');
    Route::get('/{model}/duplicate', [OverrideCommissionModelController::class, 'duplicate'])->name('duplicate');
    Route::delete('/{model}', [OverrideCommissionModelController::class, 'destroy'])->name('destroy');
});

// Model 3: Unilevel / Generation-Based MLM Commission Model Routes (/unilevel-models)
Route::prefix('unilevel-models')->name('unilevel-models.')->group(function () {
    Route::get('/create', [UniLevelCommissionModelController::class, 'create'])->name('create');
    Route::post('/', [UniLevelCommissionModelController::class, 'store'])->name('store');
    Route::post('/calculate', [UniLevelCommissionModelController::class, 'calculate'])->name('calculate');
    Route::get('/{model}', [UniLevelCommissionModelController::class, 'show'])->name('show');
    Route::get('/{model}/edit', [UniLevelCommissionModelController::class, 'edit'])->name('edit');
    Route::put('/{model}', [UniLevelCommissionModelController::class, 'update'])->name('update');
    Route::get('/{model}/duplicate', [UniLevelCommissionModelController::class, 'duplicate'])->name('duplicate');
    Route::delete('/{model}', [UniLevelCommissionModelController::class, 'destroy'])->name('destroy');
});

// Backwards-compatible /models aliases
Route::redirect('/models', '/commission-models')->name('models.index');
Route::get('/models/create', [CommissionModelController::class, 'create'])->name('models.create');
Route::post('/models', [CommissionModelController::class, 'store'])->name('models.store');
Route::post('/models/calculate', [CommissionModelController::class, 'calculate'])->name('models.calculate');
Route::get('/models/{model}', [CommissionModelController::class, 'show'])->name('models.show');
Route::get('/models/{model}/edit', [CommissionModelController::class, 'edit'])->name('models.edit');
Route::put('/models/{model}', [CommissionModelController::class, 'update'])->name('models.update');
Route::get('/models/{model}/duplicate', [CommissionModelController::class, 'duplicate'])->name('models.duplicate');
Route::delete('/models/{model}', [CommissionModelController::class, 'destroy'])->name('models.destroy');
