<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => page.props.auth.roles || []);

const sidebarOpen = ref(false);

// Role-based menu configuration
const menuItems = computed(() => {
    const hasRole = (role) => userRoles.value.includes(role);
    const hasAnyRole = (...roles) => roles.some(role => userRoles.value.includes(role));
    
    const items = [];
    
    // Dashboard - All users
    items.push({
        name: 'Dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        route: 'dashboard',
        roles: ['super_admin', 'manager', 'marketing_officer', 'staff', 'customer']
    });
    
    // HR Module - Super Admin & Manager
    if (hasAnyRole('super_admin', 'manager')) {
        items.push({
            name: 'HR',
            icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            children: [
                { name: 'Employees', route: 'employees.index' },
                { name: 'Customers', route: 'customers.index' },
                { name: 'Suppliers', route: 'suppliers.index' },
            ]
        });
    }
    
    // Inventory - Super Admin & Manager
    if (hasAnyRole('super_admin', 'manager')) {
        items.push({
            name: 'Inventory',
            icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            children: [
                { name: 'Items', route: 'items.index' },
                { name: 'Categories', route: 'categories.index' },
                { name: 'Stock', route: 'stocks.index' },
            ]
        });
    }
    
    // Purchase - Super Admin & Manager
    if (hasAnyRole('super_admin', 'manager')) {
        items.push({
            name: 'Purchase',
            icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
            route: 'purchases.index',
        });
    }
    
    // Quotations - Super Admin, Manager, Marketing
    if (hasAnyRole('super_admin', 'manager', 'marketing_officer')) {
        items.push({
            name: 'Quotations',
            icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            route: 'quotations.index',
        });
    }
    
    // Sales - Super Admin, Manager, Marketing
    if (hasAnyRole('super_admin', 'manager', 'marketing_officer')) {
        items.push({
            name: 'Sales',
            icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            route: 'sales.index',
        });
    }
    
    // Production - Super Admin & Manager
    if (hasAnyRole('super_admin', 'manager')) {
        items.push({
            name: 'Production',
            icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
            route: 'production.index',
        });
    }
    
    // Payments - Super Admin, Manager, Marketing
    if (hasAnyRole('super_admin', 'manager', 'marketing_officer')) {
        items.push({
            name: 'Payments',
            icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            route: 'payments.index',
        });
    }
    
    // Attendance - All except Customer
    if (!hasRole('customer')) {
        items.push({
            name: 'Attendance',
            icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            route: 'attendance.index',
        });
    }
    
    // Accounts - Super Admin & Manager
    if (hasAnyRole('super_admin', 'manager')) {
        items.push({
            name: 'Accounts',
            icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            route: 'accounts.index',
        });
    }
    
    // Reports - Super Admin & Manager
    if (hasAnyRole('super_admin', 'manager')) {
        items.push({
            name: 'Reports',
            icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            route: 'reports.index',
        });
    }
    
    return items.filter(item => !item.roles || item.roles.some(role => userRoles.value.includes(role)));
});

const logout = () => {
    if (confirm('আপনি কি লগআউট করতে চান?')) {
        router.post(route('logout'));
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'block' : 'hidden'" @click="sidebarOpen = false" class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"></div>
        
        <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-gray-900 lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex items-center justify-center mt-8">
                <div class="flex items-center">
                    <span class="mx-2 text-2xl font-semibold text-white">Production MS</span>
                </div>
            </div>

            <nav class="mt-10">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Menu with children -->
                    <div v-if="item.children" class="px-6 py-3">
                        <div class="flex items-center text-gray-300 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                            </svg>
                            <span class="mx-3">{{ item.name }}</span>
                        </div>
                        <div class="ml-8 mt-2 space-y-1">
                            <Link v-for="child in item.children" :key="child.name" :href="route(child.route)" 
                                class="block py-2 text-sm text-gray-400 hover:text-white">
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Single menu item -->
                    <Link v-else :href="route(item.route)" 
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                        </svg>
                        <span class="mx-3">{{ item.name }}</span>
                    </Link>
                </template>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 lg:ml-64">
            <!-- Top Navigation -->
            <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center">
                    <div class="relative">
                        <button class="flex items-center text-gray-700 dark:text-gray-200 focus:outline-none">
                            <span class="mx-2">{{ user.name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900">
                <div class="container mx-auto px-6 py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
