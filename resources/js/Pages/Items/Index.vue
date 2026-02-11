<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, defineProps } from 'vue';
import { throttle } from 'lodash';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    items: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

watch([search, typeFilter], throttle(([searchValue, typeValue]) => {
    router.get(
        route('items.index'),
        { search: searchValue, type: typeValue },
        { preserveState: true, replace: true }
    );
}, 300));

import Swal from 'sweetalert2';

const deleteItem = (id) => {
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
            router.delete(route('items.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                     Swal.fire(
                        'Deleted!',
                        'Item has been deleted.',
                        'success'
                    )
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Items" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Top Controls -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0">
                            <div class="flex items-center space-x-4">
                                <!-- Placeholder or Type Filter could go here if separate -->
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('items.create')" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Item
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

                            <div class="flex items-center space-x-4 w-full md:w-auto">
                                <!-- Type Filter -->
                                <select v-model="typeFilter" class="w-full md:w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="">All Types</option>
                                    <option value="product">Product</option>
                                    <option value="service">Service</option>
                                </select>
                                
                                <span class="text-sm text-gray-600 dark:text-gray-400">Search:</span>
                                <input v-model="search" type="text" class="w-full md:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white" placeholder="">
                            </div>
                        </div>

                        <!-- Table -->
                        <div v-if="items.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-blue-100 text-blue-800': item.type === 'product',
                                                'bg-purple-100 text-purple-800': item.type === 'service',
                                            }">
                                                {{ item.type.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.price }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span v-if="item.type === 'service'" class="text-gray-400">-</span>
                                            <span v-else>{{ item.stock_quantity }} {{ item.unit }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': item.status === 'active',
                                                'bg-red-100 text-red-800': item.status === 'inactive',
                                            }">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('items.edit', item.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                            <button @click="deleteItem(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                             <!-- Pagination moved inside to be always visible if items exist, or handle empty state differently -->
                             <div class="mt-6 flex justify-center">
                                <Pagination :links="items.links" />
                            </div>
                        </div>
                        
                         <!-- Empty State -->
                        <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                            No items found.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
