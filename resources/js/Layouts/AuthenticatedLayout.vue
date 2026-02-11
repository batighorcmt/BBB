<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => page.props.auth.roles || []);

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const userDropdownOpen = ref(false);
const userPermissions = computed(() => page.props.auth.permissions || []);

// Role-based menu configuration
const menuItems = computed(() => {
    // Helper function to check for roles
    // If no roles defined in component, fallback to empty array
    const roles = userRoles.value || [];
    const permissions = userPermissions.value || [];

    const hasRole = (role) => roles.includes(role);
    const hasAnyRole = (...checkRoles) => checkRoles.some(role => roles.includes(role));
    const hasPermission = (permission) => permissions.includes(permission);
    
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

    // Users & Roles - Logic Breakdown
    // Users List (Employees): Super Admin & Manager (and possibly others with view_employees if applicable)
    // Roles List: ONLY Super Admin
    
    const usersItems = [];
    
    if (hasAnyRole('super_admin', 'manager')) {
         usersItems.push({ name: 'Users List', route: 'users.index' });
    }
    
    if (hasRole('super_admin')) {
         usersItems.push({ name: 'Roles List', route: 'roles.index' });
    }

    if (usersItems.length > 0) {
        items.push({
            name: 'Users',
            icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            children: usersItems
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
    
    // Filter items based on roles
    return items.filter(item => {
        // If item has no specific roles defined, it's visible to everyone (or logic handled above)
        // If item has roles, check if user has at least one of them
        if (!item.roles || item.roles.length === 0) return true;
        return item.roles.some(role => (userRoles.value || []).includes(role));
    });
});


const activeMenu = ref(null);

const toggleMenu = (menuName) => {
    if (activeMenu.value === menuName) {
        activeMenu.value = null; // Collapse if already open
    } else {
        activeMenu.value = menuName; // Expand clicked menu
    }
};

// Check if a child route is active to set initial activeMenu
const checkActiveMenu = () => {
    // We can't access menuItems.value directly inside setup before it's computed? 
    // Actually we can watch the route.
    const currentRoute = route().current();
    if (!currentRoute) return;

    // Iterate through computed menu items to find containing group
    // We need to access the computed value, which might be tricky if it depends on ref updates.
    // However, on mount/watch, we can iterate.
    
    menuItems.value.forEach(item => {
        if (item.children) {
            const hasActiveChild = item.children.some(child => route().current(child.route));
            if (hasActiveChild) {
                activeMenu.value = item.name;
            }
        }
    });
};

import { onMounted, watch } from 'vue';
import Toast from '@/Components/Toast.vue'; // Import Toast

onMounted(() => {
    checkActiveMenu();
});

// Watch for route changes to update active menu (optional, if you want it to auto-expand on navigation)
// watch(() => page.url, () => checkActiveMenu()); 

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <!-- Main Layout Container - Full Screen Flex -->
    <div class="flex h-screen w-full overflow-hidden bg-gray-100 dark:bg-gray-900">
        
        <!-- Mobile Overlay -->
        <div v-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 lg:hidden transition-opacity"></div>
        
        <!-- Sidebar - Flexible Item -->
        <aside :class="[
            'bg-[#00334e] text-white transition-all duration-300 ease-in-out flex flex-col flex-shrink-0',
            // Mobile: Fixed, overlaid
            'fixed lg:static inset-y-0 left-0 z-30 h-full',
            // Mobile transform
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            // Width based on state
            sidebarCollapsed ? 'w-20' : 'w-64'
        ]">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-[#00283d] bg-[#00283d]">
                <div v-show="!sidebarCollapsed" class="text-xl font-bold text-white tracking-wider">Batighor Computers</div>
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:inline-flex text-gray-300 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <template v-for="item in menuItems" :key="item.name">
                    <!-- Menu Group with Children -->
                    <!-- Menu Group with Children -->
                    <div v-if="item.children" class="space-y-1">
                        <!-- Group Header - Clickable for toggle -->
                        <div @click="toggleMenu(item.name)" 
                             class="flex items-center justify-between px-4 py-2 cursor-pointer text-gray-300 hover:bg-[#004062] hover:text-white transition-colors rounded-lg group">
                            
                            <div class="flex items-center">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                                </svg>
                                <span v-show="!sidebarCollapsed" class="ml-3 font-medium">{{ item.name }}</span>
                            </div>

                            <!-- Expand/Collapse Icon -->
                            <div v-show="!sidebarCollapsed">
                                <svg :class="['w-4 h-4 transition-transform duration-200', activeMenu === item.name ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Children Container - Show only if activeMenu matches -->
                        <div v-show="activeMenu === item.name && !sidebarCollapsed" class="space-y-1 pl-11 pr-2">
                            <Link v-for="child in item.children" :key="child.name" :href="route(child.route)"
                                :class="[
                                    'flex items-center px-4 py-2 text-sm text-gray-400 rounded-lg hover:text-white transition-colors block',
                                    route().current(child.route) ? 'text-white font-medium' : ''
                                ]">
                                <span>{{ child.name }}</span>
                            </Link>
                        </div>
                         <!-- Mobile/Collapsed View: Show Children in Popover or similar? For now simple collapse behavior is fine, or we can just show icon without expansion for collapsed state -->
                    </div>

                    <!-- Single Menu Item -->
                    <div v-else>
                        <Link :href="route(item.route)"
                            :class="[
                                'flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-[#004062] hover:text-white transition-colors',
                                route().current(item.route) ? 'bg-[#004d7a] text-white border-l-4 border-cyan-400' : '',
                                sidebarCollapsed ? 'justify-center' : ''
                            ]">
                            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                            </svg>
                            <span v-show="!sidebarCollapsed" class="ml-3 font-medium">{{ item.name }}</span>
                        </Link>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Main Content - Takes remaining space -->
        <div class="flex flex-col flex-1 overflow-hidden">
            
            <!-- Header -->
            <header class="h-16 bg-[#00334e] border-b border-[#00283d] flex items-center justify-between px-6 flex-shrink-0 shadow-md">
                
                <!-- Left Side: Mobile Hamburger & Title/Breadcrumbs -->
                <div class="flex items-center">
                     <button @click="sidebarOpen = true" class="lg:hidden text-gray-300 hover:text-white focus:outline-none mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <!-- Optional: Add page title here for mobile if needed -->
                    <h1 class="text-xl font-bold text-white hidden sm:block" v-if="$page.props.header">{{ $page.props.header }}</h1>
                </div>
                
                <!-- Right Side: User Menu -->
                
                <!-- User Menu (Right) -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button @click="userDropdownOpen = !userDropdownOpen" 
                            class="flex items-center space-x-2 text-gray-200 hover:text-white focus:outline-none">
                            <span class="font-medium">{{ user.name }}</span>
                            <svg :class="['w-5 h-5 transition-transform', userDropdownOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Signed in as</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" :title="user.email">{{ user.name }}</p>
                            </div>
                            
                            <!-- Account Management -->
                             <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                Profile
                            </Link>

                            <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Log Out
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Heading -->
            <header class="bg-white dark:bg-gray-800 shadow relative z-10" v-if="$slots.header">
                <div class="w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <div class="w-full">
                    <slot />
                </div>
            </main>

        </div>

    </div>
    <Toast :flash="$page.props.flash" />
</template>
