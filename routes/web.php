<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pos\SellProduct;
use App\Livewire\Pos\RentProduct;
use App\Livewire\Dashboard\DailyReport;
use App\Livewire\Dashboard\CashClose;
use App\Livewire\Inventory\ProductCatalog;
use App\Livewire\Inventory\BatchProductRegistration;
use App\Livewire\Settings\UserManagement;

// Rutas públicas
Route::view('/', 'welcome')->name('home');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // POS - Vender (requiere permiso 'sell')
    Route::group(['middleware' => 'can:sell', 'prefix' => 'pos', 'as' => 'pos.'], function () {
        Route::get('sell', SellProduct::class)->name('sell');
    });

    // Rentals (requiere permiso 'rent')
    Route::group(['middleware' => 'can:rent', 'prefix' => 'pos', 'as' => 'pos.'], function () {
        Route::get('rent', RentProduct::class)->name('rent');
    });

    // Inventario y Productos (requiere permiso 'manage-products')
    Route::group(['middleware' => 'can:manage-products', 'prefix' => 'inventory', 'as' => 'inventory.'], function () {
        Route::get('catalog', ProductCatalog::class)->name('catalog');
        Route::get('batch-register', BatchProductRegistration::class)->name('batch-register');
    });

    // Reportes (requiere permiso 'view-reports')
    Route::group(['middleware' => 'can:view-reports', 'prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::get('reports', DailyReport::class)->name('reports');
    });

    // Cierre de Caja (requiere permiso 'cash-close')
    Route::group(['middleware' => 'can:cash-close', 'prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::get('cash-close', CashClose::class)->name('cash-close');
    });

    // Gestión de Usuarios - SOLO ADMIN (requiere permiso 'manage-users')
    Route::group(['middleware' => 'can:manage-users', 'prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('users', UserManagement::class)->name('users');
    });
});

require __DIR__.'/settings.php';
