<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // HR Module Routes
    Route::middleware(['can:view_employees'])->group(function () {
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    });
    
    Route::middleware(['can:view_customers'])->group(function () {
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    });
    
    Route::middleware(['can:view_suppliers'])->group(function () {
        Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    });

    // Inventory
    Route::middleware(['can:view_items'])->group(function () {
        Route::resource('items', \App\Http\Controllers\ItemController::class);
        Route::get('/categories', fn() => Inertia::render('ComingSoon', ['module' => 'Categories']))->name('categories.index');
    });

    Route::middleware(['can:view_stock'])->group(function () {
        Route::get('/stocks', fn() => Inertia::render('ComingSoon', ['module' => 'Stock']))->name('stocks.index');
    });

    // Purchase
    Route::middleware(['can:view_purchases'])->group(function () {
        Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);
    });

    // Sales
    Route::middleware(['can:view_quotations'])->group(function () {
        Route::resource('quotations', \App\Http\Controllers\QuotationController::class);
    });

    Route::middleware(['can:view_sales'])->group(function () {
        Route::resource('sales', \App\Http\Controllers\SaleController::class);
        Route::get('/api/productions/{id}', [\App\Http\Controllers\SaleController::class, 'getProductionDetails'])->name('api.productions.show');
    });

    // Production
    Route::middleware(['can:view_production'])->group(function () {
        Route::resource('production', \App\Http\Controllers\ProductionController::class);
        Route::patch('/production/{production}/status', [\App\Http\Controllers\ProductionController::class, 'updateStatus'])->name('production.status');
        Route::get('/api/quotations/{id}', [\App\Http\Controllers\ProductionController::class, 'getQuotationDetails'])->name('api.quotations.show');
    });

    // Finance & Accounts
    Route::middleware(['can:view_payments'])->group(function () {
        Route::get('/payments', fn() => Inertia::render('ComingSoon', ['module' => 'Payments']))->name('payments.index');
    });

    Route::middleware(['can:view_transactions'])->group(function () {
        Route::get('/accounts', fn() => Inertia::render('ComingSoon', ['module' => 'Accounts']))->name('accounts.index');
    });
    
    Route::middleware(['can:view_reports'])->group(function () {
        Route::get('/reports', fn() => Inertia::render('ComingSoon', ['module' => 'Reports']))->name('reports.index');
    });

    // Settings & Roles
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class);
    });

    // Users Management
    Route::middleware(['can:view_employees'])->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/status', [\App\Http\Controllers\UserController::class, 'updateStatus'])->name('users.status');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Attendance
    Route::middleware(['can:view_attendance'])->group(function () {
        Route::get('/attendance', fn() => Inertia::render('ComingSoon', ['module' => 'Attendance']))->name('attendance.index');
    });
});

require __DIR__.'/auth.php';