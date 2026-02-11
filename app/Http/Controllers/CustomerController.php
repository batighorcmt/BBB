<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('customer_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => request()->all(['search', 'trashed']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create', [
            'roles' => \Spatie\Permission\Models\Role::all()
        ]);
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
            'credit_limit' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'role' => 'nullable|exists:roles,name', // Optional, default to customer
        ]);

        // Auto-generate Customer Code
        $latest = Customer::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $validated['customer_code'] = 'CUS-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        
        $roleInfo = $validated['role'] ?? 'customer';

        // Create User
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'username' => $validated['customer_code'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'role' => $roleInfo,
            'status' => 'active',
        ]);
        
        $user->assignRole($roleInfo);

        $validated['user_id'] = $user->id;

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        $customer->load('user.roles');

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'roles' => \Spatie\Permission\Models\Role::all(),
            'userRole' => $customer->user ? $customer->user->roles->first()?->name : null
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_code' => ['required', 'string', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'role' => 'nullable|exists:roles,name',
        ]);
        
        // Update User Role if changed
        if ($customer->user) {
            $roleInfo = $validated['role'] ?? 'customer';
            $customer->user->syncRoles([$roleInfo]);
            $customer->user->update(['role' => $roleInfo]);
        }

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load('user');
        
        $sales = \App\Models\Sale::where('customer_id', $customer->id)
            ->latest()
            ->paginate(10);
            
        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'sales' => $sales,
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
