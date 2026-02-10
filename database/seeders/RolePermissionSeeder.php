<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Dashboard
            'view_dashboard',
            
            // HR Management
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
            
            // Inventory
            'view_items', 'create_items', 'edit_items', 'delete_items',
            'view_stock', 'adjust_stock',
            
            // Purchase
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            
            // Sales
            'view_quotations', 'create_quotations', 'edit_quotations', 'delete_quotations', 'approve_quotations',
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            
            // Production
            'view_production', 'create_production', 'edit_production', 'delete_production',
            'manage_production_stages',
            
            // Payments
            'view_payments', 'collect_payments', 'approve_payments', 'reject_payments',
            
            // Accounts
            'view_transactions', 'create_transactions', 'edit_transactions', 'delete_transactions',
            'view_reports', 'view_financial_reports',
            
            // Attendance
            'view_attendance', 'mark_attendance', 'approve_attendance',
            'view_leaves', 'apply_leave', 'approve_leave', 'reject_leave',
            
            // Payroll
            'view_salaries', 'process_salaries', 'approve_salaries',
            
            // Settings
            'manage_roles', 'manage_permissions', 'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        
        // 1. Super Admin - Full Access
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Manager - Most Permissions except role management
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view_dashboard',
            'view_employees', 'create_employees', 'edit_employees',
            'view_customers', 'create_customers', 'edit_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers',
            'view_items', 'create_items', 'edit_items',
            'view_stock', 'adjust_stock',
            'view_purchases', 'create_purchases', 'edit_purchases',
            'view_quotations', 'create_quotations', 'edit_quotations', 'approve_quotations',
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production', 'manage_production_stages',
            'view_payments', 'approve_payments', 'reject_payments',
            'view_transactions', 'create_transactions', 'edit_transactions',
            'view_reports', 'view_financial_reports',
            'view_attendance', 'approve_attendance',
            'view_leaves', 'approve_leave', 'reject_leave',
            'view_salaries', 'process_salaries', 'approve_salaries',
        ]);

        // 3. Marketing Officer - Sales, Quotations, Payment Collection
        $marketing = Role::create(['name' => 'marketing_officer']);
        $marketing->givePermissionTo([
            'view_dashboard',
            'view_customers',
            'view_quotations', 'create_quotations', 'edit_quotations',
            'view_sales', 'create_sales',
            'view_payments', 'collect_payments',
            'mark_attendance',
            'view_leaves', 'apply_leave',
        ]);

        // 4. Staff - Limited Access
        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'view_dashboard',
            'mark_attendance',
            'view_leaves', 'apply_leave',
            'view_salaries',
        ]);

        // 5. Customer - View Own Orders
        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'view_dashboard',
            'view_quotations',
            'view_sales',
        ]);

        // Create Demo Users
        
        // Super Admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'phone' => '01700000000',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $admin->assignRole('super_admin');

        // Manager
        $managerUser = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '01700000001',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $managerUser->assignRole('manager');

        // Marketing Officer
        $marketingUser = User::create([
            'name' => 'Marketing Officer',
            'email' => 'marketing@example.com',
            'phone' => '01700000002',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $marketingUser->assignRole('marketing_officer');

        // Staff
        $staffUser = User::create([
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'phone' => '01700000003',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $staffUser->assignRole('staff');

        // Customer
        $customerUser = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000004',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $customerUser->assignRole('customer');
    }
}
