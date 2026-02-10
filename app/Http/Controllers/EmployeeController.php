<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => request()->all(['search', 'trashed']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Employees/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone', // Phone is now username
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,terminated',
            'address' => 'nullable|string',
            'employment_type' => 'required|in:permanent,contractual,daily_wage',
        ]);

        // Generate Employee Code
        $latest = Employee::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $validated['employee_code'] = 'EMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        // Set default salary if not provided
        $validated['salary'] = $validated['salary'] ?? 0;

        // Create User
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'username' => $validated['employee_code'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        $validated['user_id'] = $user->id;
        
        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_code' => ['required', 'string', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'joining_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,terminated',
            'address' => 'nullable|string',
            'employment_type' => 'required|in:permanent,contractual,daily_wage',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
