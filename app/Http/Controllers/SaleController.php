<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Production;
use App\Models\Customer;
use App\Models\Payment;
use App\Helpers\IdGenerator;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'production', 'creator']);

        if ($request->search) {
            $query->where('sale_no', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
        }

        $sales = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        // Get completed productions that haven't been sold yet?
        // Or just all completed productions.
        // Better to check if production_id is already in sales table?
        
        $soldProductionIds = Sale::whereNotNull('production_id')->pluck('production_id');
        
        $productions = Production::with(['quotation.customer'])
            ->where('status', 'completed')
            ->whereNotIn('id', $soldProductionIds)
            ->latest()
            ->get();

        return Inertia::render('Sales/Create', [
            'productions' => $productions,
        ]);
    }

    public function getProductionDetails($id)
    {
        $production = Production::with(['items.quotationItem.item', 'quotation.customer', 'items'])->findOrFail($id);
        
        // We need GSM and Color from Items table, which is linked via QuotationItem -> Item
        // production_items has quotation_item_id.
        // quotation_items has item_id.
        // items has gsm, fabric_color.
        
        // Let's load the nested relationship: items.quotationItem.item
        
        return response()->json($production);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'production_id' => 'required|exists:productions,id',
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:wholesale,local',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'paid_amount' => 'required|numeric|min:0',
            // ... validation for others
        ]);

        DB::beginTransaction();
        try {
            $production = Production::findOrFail($validated['production_id']);
             
            // Calculate Due
            $grandTotal = $request->grand_total;
            $paid = $request->paid_amount;
            $advanceAdjusted = $request->advance_adjusted;
            $due = $grandTotal - $paid - $advanceAdjusted; // User logic: Payable = Total - Advance. Paid is separate.
            // Wait, usually: Due = (Total - Advance - Discount + Tax) - Paid.
            // User: "Grand total ... minus advance ... shows Receiveable. Then if paid, subtract paid. Remaining is Due."
            // So: Due = (GrandTotal - Advance) - Paid.
            
            // 1. Create Sale
            $saleNo = IdGenerator::generate(Sale::class, 'sale_no', 'SALE-');

            $sale = Sale::create([
                'sale_no' => $saleNo,
                'customer_id' => $validated['customer_id'],
                'production_id' => $validated['production_id'],
                'type' => $validated['type'],
                'subtotal' => $request->subtotal ?? 0,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'other_costs' => $request->other_costs ?? 0,
                'grand_total' => $grandTotal,
                'advance_adjusted' => $advanceAdjusted,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'status' => 'completed',
                'note' => $request->note,
                'created_by' => Auth::id(),
            ]);

            // Sync status to "Delivered"
            $production->update(['status' => 'delivered']);
            $production->quotation()->update(['status' => 'delivered']);

            // 2. Create Items
            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'production_item_id' => $item['production_item_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'size' => $item['size'],
                    'gsm' => $item['gsm'],
                    'color' => $item['color'],
                    'envelope_weight' => $item['envelope_weight'] ?? 0,
                    'envelope_price' => $item['envelope_price'] ?? 0,
                    'loop_weight' => $item['loop_weight'] ?? 0,
                    'loop_price' => $item['loop_price'] ?? 0,
                    'print_cost' => $item['print_cost'] ?? 0,
                    'sewing_cost' => $item['sewing_cost'] ?? 0,
                    'quantity' => $item['quantity'],
                    'price_per_piece' => $item['price_per_piece'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // 3. Update Customer Balance
            // Logic: "Remaining due adds to customer balance".
            // If Due is positive, customer owes money -> Increase balance (Debit).
            // Schema: current_balance. 
            // Assuming positive balance means Customer OWES us.
            $customer = Customer::findOrFail($validated['customer_id']);
            $customer->current_balance += $due;
            $customer->save();

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Sale creation failed: ' . $e->getMessage()]);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['items', 'customer', 'production', 'creator']);
        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }

    public function edit(Sale $sale)
    {
        $sale->load(['items', 'customer', 'production.items.quotationItem.item']);
        
        $soldProductionIds = Sale::where('id', '!=', $sale->id)
            ->whereNotNull('production_id')
            ->pluck('production_id');
        
        $productions = Production::with(['quotation.customer'])
            ->where('status', 'completed')
            ->whereNotIn('id', $soldProductionIds)
            ->latest()
            ->get();

        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'productions' => $productions,
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'production_id' => 'required|exists:productions,id',
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:wholesale,local',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($sale->customer_id);
            $customer->current_balance -= $sale->due_amount;
            
            $grandTotal = $request->grand_total;
            $paid = $request->paid_amount;
            $advanceAdjusted = $request->advance_adjusted;
            $due = $grandTotal - $paid - $advanceAdjusted;

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'production_id' => $validated['production_id'],
                'type' => $validated['type'],
                'subtotal' => $request->subtotal ?? 0,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'other_costs' => $request->other_costs ?? 0,
                'grand_total' => $grandTotal,
                'advance_adjusted' => $advanceAdjusted,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'note' => $request->note,
            ]);

            SaleItem::where('sale_id', $sale->id)->delete();
            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'production_item_id' => $item['production_item_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'size' => $item['size'],
                    'gsm' => $item['gsm'],
                    'color' => $item['color'],
                    'envelope_weight' => $item['envelope_weight'] ?? 0,
                    'envelope_price' => $item['envelope_price'] ?? 0,
                    'loop_weight' => $item['loop_weight'] ?? 0,
                    'loop_price' => $item['loop_price'] ?? 0,
                    'print_cost' => $item['print_cost'] ?? 0,
                    'sewing_cost' => $item['sewing_cost'] ?? 0,
                    'quantity' => $item['quantity'],
                    'price_per_piece' => $item['price_per_piece'],
                    'total_price' => $item['total_price'],
                ]);
            }

            if ($validated['customer_id'] != $customer->id) {
                $customer->save();
                $customer = Customer::findOrFail($validated['customer_id']);
                $customer->current_balance += $due;
            } else {
                $customer->current_balance += $due;
            }
            $customer->save();

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    public function destroy(Sale $sale)
    {
        // ... (existing content)
    }

    public function receivePayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create Payment record
            $paymentNo = IdGenerator::generate(Payment::class, 'payment_no', 'PAY-');
            Payment::create([
                'payment_no' => $paymentNo,
                'payable_id' => $sale->id,
                'payable_type' => Sale::class,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'],
                'notes' => $validated['notes'],
                'status' => 'approved',
                'collected_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // 2. Update Sale
            $sale->paid_amount += $validated['amount'];
            $sale->due_amount -= $validated['amount'];
            $sale->save();

            // 3. Update Customer Balance
            $customer = $sale->customer;
            if ($customer) {
                $customer->current_balance -= $validated['amount'];
                $customer->save();
            }

            DB::commit();
            return back()->with('success', 'Payment received successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Payment failed: ' . $e->getMessage()]);
        }
    }
}
