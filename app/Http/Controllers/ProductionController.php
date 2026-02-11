<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\ProductionItem;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['quotation.customer', 'creator'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Production/Index', [
            'productions' => $productions,
        ]);
    }

    public function create()
    {
        // Fetch quotations that are 'pending' or 'working'? 
        // User said: "Quotations that are not complete (pending status)". relative to production.
        // Actually user said: "quotations ... pending status". 
        // But once production starts, it becomes 'working'. So we might want to allow adding items to 'working' ones too?
        // User said: "Where multiple products can be added to production at once".
        // Let's stick to 'pending' quotations to start a new production.
        
        $quotations = Quotation::with('customer')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Production/Create', [
            'quotations' => $quotations,
        ]);
    }

    public function getQuotationDetails($id)
    {
        $quotation = Quotation::with(['items.item', 'customer'])->findOrFail($id);
        return response()->json($quotation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'production_date' => 'nullable|date', // Not in schema, but useful?
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'status' => 'required|in:pending,working,completed,cancelled',
            // ... validation for costs ...
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::findOrFail($validated['quotation_id']);
            
            // 1. Create Production
            $production = Production::create([
                'production_no' => 'PROD-' . time(),
                'quotation_id' => $validated['quotation_id'],
                'status' => $validated['status'], // Default pending? User said default pending in list.
                'total_cost' => $request->total_cost ?? 0,
                'advance_amount' => $quotation->advance_amount ?? 0,
                'final_cost' => ($request->total_cost ?? 0) - ($quotation->advance_amount ?? 0),
                'note' => $request->note,
                'created_by' => Auth::id(),
            ]);

            // 2. Create Items
            foreach ($request->items as $item) {
                ProductionItem::create([
                    'production_id' => $production->id,
                    'quotation_item_id' => $item['quotation_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'envelope_weight' => $item['envelope_weight'] ?? 0,
                    'envelope_price' => $item['envelope_price'] ?? 0,
                    'loop_weight' => $item['loop_weight'] ?? 0,
                    'loop_price' => $item['loop_price'] ?? 0,
                    'print_cost' => $item['print_cost'] ?? 0,
                    'sewing_cost' => $item['sewing_cost'] ?? 0,
                    'price_per_piece' => $item['price_per_piece'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                    'wastage_kg' => $item['wastage_kg'] ?? 0,
                    'wastage_piece' => $item['wastage_piece'] ?? 0,
                ]);
            }

            // 3. Update Quotation Status
            // "Where quotation id from production starts, quotation status will show 'working'"
            // "Once production completed, quotation status 'production_ready'"
            
            if ($production->status === 'completed') {
                $quotation->update(['status' => 'production_ready']);
            } else {
                 $quotation->update(['status' => 'working']);
            }

            DB::commit();
            return redirect()->route('production.index')->with('success', 'Production created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Production creation failed: ' . $e->getMessage()]);
        }
    }

    public function show(Production $production)
    {
        $production->load(['items', 'quotation.customer', 'creator']);
        return Inertia::render('Production/Show', [
            'production' => $production,
        ]);
    }

    public function edit(Production $production)
    {
        $production->load(['items', 'quotation.customer']);
        return Inertia::render('Production/Edit', [
            'production' => $production,
        ]);
    }

    public function update(Request $request, Production $production)
    {
         // Similar to store but updates
         $validated = $request->validate([
             'status' => 'required|in:pending,working,completed,cancelled',
             'items' => 'required|array',
         ]);

         DB::beginTransaction();
         try {
             $production->update([
                 'status' => $validated['status'],
                 'total_cost' => $request->total_cost ?? 0,
                 'final_cost' => ($request->total_cost ?? 0) - $production->advance_amount,
                 'note' => $request->note,
             ]);

             // Sync items? Or delete all and recreate? Simplest is delete and recreate for now, or update if ID exists.
             // For simplicity in this iteration: Delete and Recreate items.
             ProductionItem::where('production_id', $production->id)->delete();
             
             foreach ($request->items as $item) {
                 ProductionItem::create([
                    'production_id' => $production->id,
                    'quotation_item_id' => $item['quotation_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'envelope_weight' => $item['envelope_weight'] ?? 0,
                    'envelope_price' => $item['envelope_price'] ?? 0,
                    'loop_weight' => $item['loop_weight'] ?? 0,
                    'loop_price' => $item['loop_price'] ?? 0,
                    'print_cost' => $item['print_cost'] ?? 0,
                    'sewing_cost' => $item['sewing_cost'] ?? 0,
                    'price_per_piece' => $item['price_per_piece'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                    'wastage_kg' => $item['wastage_kg'] ?? 0,
                    'wastage_piece' => $item['wastage_piece'] ?? 0,
                ]);
             }

             // Update Quotation Status
             $quotation = $production->quotation;
             if ($production->status === 'completed') {
                 $quotation->update(['status' => 'production_ready']);
             } elseif ($production->status === 'cancelled') {
                 // Revert to pending?
                  $quotation->update(['status' => 'pending']);
             } else {
                 $quotation->update(['status' => 'working']);
             }

             DB::commit();
             return redirect()->route('production.index')->with('success', 'Production updated successfully.');

         } catch (\Exception $e) {
             DB::rollBack();
             return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()]);
         }
    }

    public function destroy(Production $production)
    {
        DB::transaction(function () use ($production) {
            // Revert quotation status
             $production->quotation()->update(['status' => 'pending']);
             $production->delete();
        });
        
        return redirect()->route('production.index')->with('success', 'Production deleted successfully.');
    }
}
