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
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::resource('items', \App\Http\Controllers\ItemController::class);

    // Placeholder routes for menu items (to be implemented)
    Route::get('/categories', fn() => Inertia::render('ComingSoon', ['module' => 'Categories']))->name('categories.index');
    Route::get('/stocks', fn() => Inertia::render('ComingSoon', ['module' => 'Stock']))->name('stocks.index');
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);
    Route::get('/quotations', fn() => Inertia::render('ComingSoon', ['module' => 'Quotations']))->name('quotations.index');
    Route::get('/sales', fn() => Inertia::render('ComingSoon', ['module' => 'Sales']))->name('sales.index');
    Route::get('/production', fn() => Inertia::render('ComingSoon', ['module' => 'Production']))->name('production.index');
    Route::get('/payments', fn() => Inertia::render('ComingSoon', ['module' => 'Payments']))->name('payments.index');
    Route::get('/attendance', fn() => Inertia::render('ComingSoon', ['module' => 'Attendance']))->name('attendance.index');
    Route::get('/accounts', fn() => Inertia::render('ComingSoon', ['module' => 'Accounts']))->name('accounts.index');
    Route::get('/reports', fn() => Inertia::render('ComingSoon', ['module' => 'Reports']))->name('reports.index');
});

require __DIR__.'/auth.php';