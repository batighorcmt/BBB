<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->when(request('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Items/Index', [
            'items' => $items,
            'filters' => request()->all(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Items/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            // Specifications (Nullable)
            'bag_type' => 'nullable|string|max:100',
            'usage' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'capacity' => 'nullable|string|max:100',
            'gsm' => 'nullable|string|max:50',
            'fabric_color' => 'nullable|string|max:50',
            'fabric_quality' => 'nullable|string|max:100',
            'lamination' => 'nullable|string|max:100',
            'print_type' => 'nullable|string|max:100',
            'print_color_count' => 'nullable|string|max:50',
            'print_sides' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:255',
            'design_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,ai,psd|max:10240', // 10MB max
            'handle_type' => 'nullable|string|max:100',
            'handle_color' => 'nullable|string|max:50',
            'stitching_type' => 'nullable|string|max:100',
            'bottom_finish' => 'nullable|string|max:100',
        ]);

        // Auto-generate Item Code (ITM-XXXX)
        $latest = Item::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $validated['code'] = 'ITM-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        // Force stock to 0 if service
        if ($validated['type'] === 'service') {
            $validated['stock_quantity'] = 0;
        }

        // Default null numeric values to 0
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['cost'] = $validated['cost'] ?? 0;

        // Handle File Upload
        if ($request->hasFile('design_file')) {
            $path = $request->file('design_file')->store('design_files', 'public');
            $validated['design_file'] = $path;
        }

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        return Inertia::render('Items/Edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            // Specifications (Nullable)
            'bag_type' => 'nullable|string|max:100',
            'usage' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'capacity' => 'nullable|string|max:100',
            'gsm' => 'nullable|string|max:50',
            'fabric_color' => 'nullable|string|max:50',
            'fabric_quality' => 'nullable|string|max:100',
            'lamination' => 'nullable|string|max:100',
            'print_type' => 'nullable|string|max:100',
            'print_color_count' => 'nullable|string|max:50',
            'print_sides' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:255',
            'design_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,ai,psd|max:10240', // 10MB max
            'handle_type' => 'nullable|string|max:100',
            'handle_color' => 'nullable|string|max:50',
            'stitching_type' => 'nullable|string|max:100',
            'bottom_finish' => 'nullable|string|max:100',
        ]);

        // Force stock to 0 if service
        if ($validated['type'] === 'service') {
            $validated['stock_quantity'] = 0;
        }

        // Handle File Upload
        if ($request->hasFile('design_file')) {
            // Delete old file if exists
            if ($item->design_file) {
                 // Storage::disk('public')->delete($item->design_file); // Ensure Storage facade is imported if uncommenting
            }
            $path = $request->file('design_file')->store('design_files', 'public');
            $validated['design_file'] = $path;
        }

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
