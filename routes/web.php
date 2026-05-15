<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RequireActiveAccount;
use Illuminate\Support\Facades\Route;

// ─── Pública ──────────────────────────────────────────────────────────────────


// ─── Autenticado, SIN cuenta activa requerida ─────────────────────────────────
// Aquí vive el selector — es el paso previo a tener cuenta en sesión
Route::middleware(['auth'])->group(function () {
    Route::get('/accounts/select', [AccountController::class, 'select'])
        ->name('accounts.select');
    Route::post('/accounts/select', [AccountController::class, 'setActive'])
        ->name('accounts.set-active');
});

// ─── Autenticado + cuenta activa en sesión ────────────────────────────────────
Route::middleware(['auth', RequireActiveAccount::class])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/', fn () => view('dashboard'))->name('home');

    // Usuarios — solo owner/admin (Gate verifica en middleware Y en componente)
    Route::middleware(['can:manage-account-users'])->group(function () {
        Route::get('/users',                [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create',         [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{userId}/edit',  [UserController::class, 'edit'])->name('users.edit');
    });

    /*
    // POS
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('sell', SellProduct::class)->name('sell');
        Route::get('rent', RentProduct::class)->name('rent');
    });

    // Inventario
    Route::prefix('inventory')->name('inventory.')->middleware('can:manage-products')->group(function () {
        Route::get('catalog',        ProductCatalog::class)->name('catalog');
        Route::get('batch-register', BatchProductRegistration::class)->name('batch-register');
    });

    // Reportes
    Route::prefix('reports')->name('reports.')->middleware('can:view-reports')->group(function () {
        Route::get('daily', DailyReport::class)->name('daily');
    });

    // Cierre de caja
    Route::get('cash-close', CashClose::class)
        ->middleware('can:cash-close')
        ->name('cash-close');
    */
});

require __DIR__ . '/settings.php';