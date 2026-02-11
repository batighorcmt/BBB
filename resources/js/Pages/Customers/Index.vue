<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, defineProps } from 'vue';
import { throttle } from 'lodash';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, throttle((value) => {
    router.get(
        route('customers.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));

import Swal from 'sweetalert2';

const deleteCustomer = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('customers.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                     Swal.fire(
                        'Deleted!',
                        'Customer has been deleted.',
                        'success'
                    )
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Customers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Customers List</h2>
                <div class="text-sm text-gray-500">Home > Import Customers > Customers List</div>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Top Controls -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0">
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span>View Account Receivable Customers</span>
                                </label>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('customers.create')" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create Customer
                                </Link>
                            </div>
                        </div>

                        <!-- Filter & Search Bar -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Show</span>
                                <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white">
                                    <option>10</option>
                                    <option>25</option>
                                    <option>50</option>
                                    <option>100</option>
                                </select>
                                <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
                            </div>

                            <div class="flex flex-wrap gap-2 justify-center md:justify-end">
                                <button class="px-3 py-1 bg-teal-500 text-white text-xs rounded hover:bg-teal-600">Copy</button>
                                <button class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600">Excel</button>
                                <button class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">PDF</button>
                                <button class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">Print</button>
                                <button class="px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600">CSV</button>
                                <button class="px-3 py-1 bg-indigo-500 text-white text-xs rounded hover:bg-indigo-600">Columns</button>
                            </div>

                            <div class="flex items-center space-x-2 w-full md:w-auto">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Search:</span>
                                <input v-model="search" type="text" class="w-full md:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white" placeholder="">
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto md:overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Customer ID</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Customer Name</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Mobile</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Email</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Location</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Credit Limit</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Previous Due</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Sales Return Due(+)</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Advance</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Status</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(customer, index) in customers.data" :key="customer.id" :class="index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700/50'" class="hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">{{ customer.customer_code }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ customer.name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ customer.phone }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ customer.email }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ customer.city || '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                            <span class="px-2 py-1 bg-gray-200 rounded text-xs text-gray-700 font-bold" v-if="!customer.credit_limit || customer.credit_limit == 0">No Limit</span>
                                            <span v-else>{{ customer.credit_limit }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ customer.opening_balance || '0.00' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">0.00</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">0.00</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-center">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-bold text-white rounded': true,
                                                'bg-green-500': customer.status === 'active',
                                                'bg-red-500': customer.status === 'inactive',
                                            }">
                                                {{ customer.status.charAt(0).toUpperCase() + customer.status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="relative inline-block text-left group">
                                                <button class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-[#00334e] rounded-md hover:bg-[#00283d] focus:outline-none">
                                                    Action
                                                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <!-- Dropdown menu -->
                                                <div class="origin-top-right absolute right-0 mt-0 pt-2 w-48 z-50 hidden group-hover:block hover:block">
                                                    <div class="rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                                        <Link :href="route('customers.show', customer.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">View Account</Link>
                                                        <Link :href="route('customers.edit', customer.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Edit</Link>
                                                        <button @click="deleteCustomer(customer.id)" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100 hover:text-red-900" role="menuitem">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="customers.data.length === 0">
                                        <td colspan="12" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No customers found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col md:flex-row justify-between items-center">
                            <div class="text-sm text-gray-700 dark:text-gray-400 mb-4 md:mb-0">
                                Showing 1 to {{ customers.data.length }} of {{ customers.total }} entries
                            </div>
                            <div v-if="customers.links.length > 3" class="flex justify-center">
                                <template v-for="(link, key) in customers.links" :key="key">
                                    <div v-if="link.url === null" class="mr-1 px-3 py-1 text-sm leading-4 text-gray-400 border rounded bg-white" v-html="link.label" />
                                    <Link v-else :class="{ 'bg-blue-600 text-white border-blue-600': link.active, 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active }" class="mr-1 px-3 py-1 text-sm leading-4 border rounded shadow-sm focus:border-indigo-500 focus:text-indigo-500" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
