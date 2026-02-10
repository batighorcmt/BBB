<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalSales' => method_exists(Sale::class, 'count') ? Sale::count() : 0,
            'pendingOrders' => method_exists(Sale::class, 'where') ? Sale::where('status', 'pending')->count() : 0,
            'totalCustomers' => method_exists(Customer::class, 'count') ? Customer::count() : 0,
            'lowStockItems' => method_exists(Stock::class, 'whereRaw') ? Stock::whereRaw('quantity <= 10')->count() : 0,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
