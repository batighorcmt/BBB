<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $quotations = Quotation::with(['customer', 'creator'])
            ->when($user->hasRole('customer'), function ($query) use ($user) {
                // Customers see only their own quotations
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->where('phone', $user->phone); // Assuming phone links user to customer
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->where('quotation_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $customers = [];
        
        // If user is admin/staff, load all customers. 
        // If customer, we might just load their own profile or handle it in frontend.
        if ($user->can('view_customers')) {
            $customers = Customer::all();
        }

        return Inertia::render('Quotations/Create', [
            'customers' => $customers,
            'items' => Item::where('status', 'active')->get(), // Send basic item info for dropdown
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isCustomer = $user->hasRole('customer');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            // Basic specs
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.gsm' => 'nullable|string',
            'items.*.print_color' => 'nullable|string',
            'items.*.print_side' => 'nullable|string',
            // Pricing
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        // Security Check: If Customer, ensure prices match database prices
        if ($isCustomer) {
            foreach ($validated['items'] as $itemData) {
                $dbItem = Item::find($itemData['item_id']);
                if (abs($dbItem->price - $itemData['unit_price']) > 0.01) {
                     return back()->withErrors(['items' => 'Price manipulation detected. Please refresh and try again.']);
                }
            }
        }

        DB::beginTransaction();

        try {
            // Auto-generate ID using Helper
            $quotationNo = \App\Helpers\IdGenerator::generate(Quotation::class, 'quotation_no', 'QT-');

            // Calculate totals server-side to be safe
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            
            // Allow frontend to send subtotal but strictly we should recalculate. 
            // Using frontend subtotal for now but could verify.
            
            $otherCharges = $validated['other_charges'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $total = ($subtotal + $otherCharges) - $discount;
            $tax = 0; // Implement if needed

            $quotation = Quotation::create([
                'quotation_no' => $quotationNo,
                'customer_id' => $validated['customer_id'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'advance_amount' => $validated['advance_amount'] ?? 0,
                'status' => 'pending',
                'notes' => $validated['notes'],
                'terms_conditions' => $validated['terms_conditions'],
                'created_by' => $user->id,
            ]);

            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $item['item_id'],
                    'description' => Item::find($item['item_id'])->name, // Fallback or custom desc
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'gsm' => $item['gsm'],
                    'print_color' => $item['print_color'],
                    'print_side' => $item['print_side'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create quotation: ' . $e->getMessage()]);
        }
    }

    public function edit(Quotation $quotation)
    {
        $user = Auth::user();
        
        // Authorization check (optional but recommended)
        if ($user->hasRole('customer') && $quotation->customer->phone !== $user->phone) {
            abort(403);
        }

        $quotation->load('items');

        $customers = [];
        if ($user->can('view_customers')) {
            $customers = Customer::all();
        }

        return Inertia::render('Quotations/Edit', [
            'quotation' => $quotation,
            'customers' => $customers,
            'items' => Item::where('status', 'active')->get(),
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $user = Auth::user();
        $isCustomer = $user->hasRole('customer');

        // Authorization check
        if ($isCustomer && $quotation->customer->phone !== $user->phone) {
            abort(403);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.gsm' => 'nullable|string',
            'items.*.print_color' => 'nullable|string',
            'items.*.print_side' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        // Security Check for Customers
        if ($isCustomer) {
            foreach ($validated['items'] as $itemData) {
                $dbItem = Item::find($itemData['item_id']);
                // Allow small floating point diff
                if (abs($dbItem->price - $itemData['unit_price']) > 0.01) {
                     return back()->withErrors(['items' => 'Price manipulation detected. Please refresh and try again.']);
                }
            }
        }

        DB::beginTransaction();

        try {
            // Recalculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            
            $otherCharges = $validated['other_charges'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $total = ($subtotal + $otherCharges) - $discount;

            $quotation->update([
                'customer_id' => $validated['customer_id'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'advance_amount' => $validated['advance_amount'] ?? 0,
                'notes' => $validated['notes'],
                'terms_conditions' => $validated['terms_conditions'],
            ]);

            // Sync items: Delete old, create new (simpler than update existing)
            $quotation->items()->delete();

            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $item['item_id'],
                    'description' => Item::find($item['item_id'])->name,
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'gsm' => $item['gsm'],
                    'print_color' => $item['print_color'],
                    'print_side' => $item['print_side'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('quotations.index')->with('success', 'Quotation updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update quotation: ' . $e->getMessage()]);
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.item', 'creator']);
        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation
        ]);
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,working,production_ready,delivered,cancelled',
        ]);

        $quotation->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated successfully.');
    }
}
