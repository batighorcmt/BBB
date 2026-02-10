<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return redirect()->route('login');
});

// Database Test Route
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $tableCount = count($tables);
        $userCount = Schema::hasTable('users') ? DB::table('users')->count() : 'Table not found';
        
        return response()->json([
            'status' => 'success',
            'message' => "Successfully connected to database: $dbName",
            'table_count' => $tableCount,
            'user_count' => $userCount,
            'tables' => array_map(fn($t) => array_values((array)$t)[0], $tables),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Placeholder routes for menu items (to be implemented)
    Route::get('/employees', fn() => Inertia::render('ComingSoon', ['module' => 'Employees']))->name('employees.index');
    Route::get('/customers', fn() => Inertia::render('ComingSoon', ['module' => 'Customers']))->name('customers.index');
    Route::get('/suppliers', fn() => Inertia::render('ComingSoon', ['module' => 'Suppliers']))->name('suppliers.index');
    Route::get('/items', fn() => Inertia::render('ComingSoon', ['module' => 'Items']))->name('items.index');
    Route::get('/categories', fn() => Inertia::render('ComingSoon', ['module' => 'Categories']))->name('categories.index');
    Route::get('/stocks', fn() => Inertia::render('ComingSoon', ['module' => 'Stock']))->name('stocks.index');
    Route::get('/purchases', fn() => Inertia::render('ComingSoon', ['module' => 'Purchases']))->name('purchases.index');
    Route::get('/quotations', fn() => Inertia::render('ComingSoon', ['module' => 'Quotations']))->name('quotations.index');
    Route::get('/sales', fn() => Inertia::render('ComingSoon', ['module' => 'Sales']))->name('sales.index');
    Route::get('/production', fn() => Inertia::render('ComingSoon', ['module' => 'Production']))->name('production.index');
    Route::get('/payments', fn() => Inertia::render('ComingSoon', ['module' => 'Payments']))->name('payments.index');
    Route::get('/attendance', fn() => Inertia::render('ComingSoon', ['module' => 'Attendance']))->name('attendance.index');
    Route::get('/accounts', fn() => Inertia::render('ComingSoon', ['module' => 'Accounts']))->name('accounts.index');
    Route::get('/reports', fn() => Inertia::render('ComingSoon', ['module' => 'Reports']))->name('reports.index');
});

require __DIR__.'/auth.php';