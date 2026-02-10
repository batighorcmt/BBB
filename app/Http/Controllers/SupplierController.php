<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('supplier_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
            
        // Ensure appends are visible if not automatically serializing
        $suppliers->getCollection()->each->append('total_due');

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => request()->all(['search', 'trashed']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        // Auto-generate Supplier Code
        $latest = Supplier::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $validated['supplier_code'] = 'SUP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        // Create User
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'username' => $validated['supplier_code'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'role' => 'supplier',
            'status' => 'active',
        ]);

        $validated['user_id'] = $user->id;

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supplier_code' => ['required', 'string', Rule::unique('suppliers')->ignore($supplier->id)],
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
