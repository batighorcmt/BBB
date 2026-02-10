<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'creator'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function create()
    {
        return Inertia::render('Purchases/Create', [
            'suppliers' => \App\Models\Supplier::all(),
            'items' => \App\Models\Item::all(),
            'paymentMethods' => ['cash', 'bank', 'mobile_banking', 'cheque'],
            'accounts' => [], // Placeholder for accounts if implemented
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payment_note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Calculate Totals
            $subtotal = 0;
            $totalQty = 0;

            foreach ($validated['items'] as $item) {
                $qty = $item['quantity'];
                $price = $item['unit_price'];
                $itemTotal = $qty * $price;
                
                // Item level discount/tax logic can be added here if needed, 
                // for now assuming basic line total = qty * price
                // If the UI sends calculated line totals, validte them.
                
                $subtotal += $itemTotal;
                $totalQty += $qty;
            }

            $discountAmount = 0;
            if ($validated['discount_type'] === 'fixed') {
                $discountAmount = $validated['discount'] ?? 0;
            } else {
                $discountAmount = ($subtotal * ($validated['discount'] ?? 0)) / 100;
            }

            $tax = 0; // Global tax if any, currently simple
            $otherCharges = $validated['other_charges'] ?? 0;
            $grandTotal = ($subtotal + $otherCharges + $tax) - $discountAmount;
            
            $paid = $validated['paid_amount'] ?? 0;
            $due = $grandTotal - $paid;
            $status = $due <= 0 ? 'completed' : 'pending';

            // Create Purchase
            $purchase = Purchase::create([
                'purchase_no' => 'PUR-' . time(), // Simple unique generator
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'reference_no' => $validated['reference_no'],
                'total_qty' => $totalQty,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'discount_type' => $validated['discount_type'],
                'discount_amount' => $validated['discount'] ?? 0, // Store original value
                'other_charges' => $otherCharges,
                'tax' => $tax,
                'total' => $grandTotal,
                'paid' => $paid,
                'due' => $due,
                'status' => $status,
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Create Purchase Items & Update Stock
            foreach ($validated['items'] as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'item_discount' => $itemData['discount'] ?? 0,
                    'item_tax' => $itemData['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);

                // Update Item Stock and Cost
                $itemModel = \App\Models\Item::find($itemData['item_id']);
                if ($itemModel && $itemModel->type === 'product') {
                    $itemModel->increment('stock_quantity', $itemData['quantity']);
                    // Optional: Update weighted average cost here
                    $itemModel->cost = $itemData['unit_price']; // Updating last cost for simplicity
                    $itemModel->save();
                }
            }

            // Create Payment if paid amount > 0
            if ($paid > 0) {
                Payment::create([
                    'payment_no' => 'PAY-' . time(),
                    'payable_id' => $purchase->id,
                    'payable_type' => Purchase::class,
                    'amount' => $paid,
                    'payment_date' => $validated['purchase_date'],
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'status' => 'approved',
                    'notes' => $validated['payment_note'],
                    'collected_by' => Auth::id(), // Or approved_by
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Purchase failed: ' . $e->getMessage()]);
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.item', 'payments']);
        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }
}
