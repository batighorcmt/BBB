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
            'totalSalesAmount' => \App\Models\Sale::sum('grand_total'),
            'totalSalesDue' => \App\Models\Sale::sum('due_amount'),
            'totalPurchaseAmount' => \App\Models\Purchase::sum('total'),
            'totalPurchaseDue' => \App\Models\Purchase::sum('due'),
            'pendingQuotations' => \App\Models\Quotation::where('status', 'pending')->count(),
            'activeCustomers' => \App\Models\Customer::where('status', 'active')->count(),
            'totalSales' => \App\Models\Sale::count(),
            'totalCustomers' => \App\Models\Customer::count(),
            'lowStockItems' => \App\Models\Stock::whereRaw('quantity <= 10')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}

