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
            'totalSales' => Sale::count(),
            'pendingOrders' => Sale::where('status', 'pending')->count(),
            'totalCustomers' => Customer::count(),
            'lowStockItems' => Stock::whereRaw('quantity <= 10')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
