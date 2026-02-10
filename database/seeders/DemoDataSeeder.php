<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Categories (idempotent)
        $categoryId = null;
        if (Schema::hasTable('categories')) {
            $category = DB::table('categories')->where('code', 'GEN')->first();
            if ($category) {
                $categoryId = $category->id;
            } else {
                $categoryId = DB::table('categories')->insertGetId([
                    'name' => 'General',
                    'code' => 'GEN',
                    'description' => 'General category',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Items
        // Items (idempotent)
        $itemId = null;
        if (Schema::hasTable('items')) {
            $existingItem = DB::table('items')->where('item_code', 'ITEM-001')->first();
            if ($existingItem) {
                $itemId = $existingItem->id;
            } else {
                $itemId = DB::table('items')->insertGetId([
                    'category_id' => $categoryId,
                    'item_code' => 'ITEM-001',
                    'name' => 'Sample Item',
                    'type' => 'finished_good',
                    'unit' => 'pcs',
                    'purchase_price' => 10.00,
                    'sale_price' => 15.00,
                    'reorder_level' => 5,
                    'description' => 'A demo item',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Stocks
        if ($itemId && Schema::hasTable('stocks')) {
            DB::table('stocks')->updateOrInsert(
                ['item_id' => $itemId],
                ['quantity' => 100, 'average_cost' => 10.00, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Customers
        if (Schema::hasTable('customers')) {
            DB::table('customers')->updateOrInsert(
                ['customer_code' => 'CUST-001'],
                [
                    'name' => 'Demo Customer',
                    'company_name' => 'Demo Co',
                    'phone' => '01710000000',
                    'email' => 'demo.customer@example.com',
                    'address' => 'Demo address',
                    'credit_limit' => 0,
                    'opening_balance' => 0,
                    'status' => 'active',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Employees
        if (Schema::hasTable('employees')) {
            DB::table('employees')->updateOrInsert(
                ['employee_code' => 'EMP-001'],
                [
                    'name' => 'Demo Employee',
                    'phone' => '01720000000',
                    'email' => 'demo.employee@example.com',
                    'designation' => 'Staff',
                    'salary' => 0,
                    'status' => 'active',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Helpful notice in logs
        if (Schema::hasTable('categories') || Schema::hasTable('items') || Schema::hasTable('customers') || Schema::hasTable('employees')) {
            info('DemoDataSeeder: inserted demo rows where tables exist.');
        } else {
            info('DemoDataSeeder: no target tables exist to seed.');
        }
    }
}
