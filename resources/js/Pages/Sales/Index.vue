<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { throttle } from 'lodash';
import Swal from 'sweetalert2';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, throttle((value) => {
    router.get(
        route('sales.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'bg-green-500';
        case 'pending': return 'bg-yellow-500';
        case 'cancelled': return 'bg-red-500';
        case 'delivered': return 'bg-teal-500';
        default: return 'bg-gray-500';
    }
};

const deleteSale = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('sales.destroy', id), {
                onSuccess: () => Swal.fire('Deleted!', 'Sale has been deleted.', 'success'),
                onError: () => Swal.fire('Error!', 'Failed to delete sale.', 'error')
            });
        }
    });
};
</script>

<template>
    <Head title="Sales List" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sales List</h2>
                <div class="text-sm text-gray-500">Home > Finance > Sales</div>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Top Controls -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0">
                            <div></div>
                            <div class="flex space-x-2">
                                <Link :href="route('sales.create')" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create Sale
                                </Link>
                            </div>
                        </div>

                        <!-- Filter & Search Bar -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                <span>Show</span>
                                <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white">
                                    <option>10</option>
                                    <option>25</option>
                                    <option>50</option>
                                </select>
                                <span>entries</span>
                            </div>

                            <div class="flex flex-wrap gap-2 justify-center">
                                <button class="px-3 py-1 bg-teal-500 text-white text-xs rounded hover:bg-teal-600">Copy</button>
                                <button class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600">Excel</button>
                                <button class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">PDF</button>
                                <button class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">Print</button>
                            </div>

                            <div class="flex items-center space-x-2 w-full md:w-auto">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Search:</span>
                                <input v-model="search" type="text" class="w-full md:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white" placeholder="No or Customer...">
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto md:overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-200 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Sale No</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Customer</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Paid</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Due</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:border-gray-600">Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(sale, index) in sales.data" :key="sale.id" :class="index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700/50'" class="hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 font-medium text-indigo-600 hover:underline">
                                            <Link :href="route('sales.show', sale.id)">{{ sale.sale_no }}</Link>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">{{ sale.customer?.name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 capitalize">{{ sale.type }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 font-bold">{{ sale.grand_total }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-green-600 font-bold">{{ sale.paid_amount }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-red-600 font-bold">{{ sale.due_amount }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-center">
                                            <span :class="['px-2 py-1 text-xs font-bold text-white rounded', getStatusColor(sale.status)]">
                                                {{ sale.status.charAt(0).toUpperCase() + sale.status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="relative inline-block text-left group">
                                                <button class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-[#00334e] rounded-md hover:bg-[#00283d]">
                                                    Action
                                                    <svg class="-mr-1 ml-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </button>
                                                <div class="origin-top-right absolute right-0 mt-0 pt-2 w-48 z-50 hidden group-hover:block hover:block">
                                                    <div class="rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1">
                                                        <Link :href="route('sales.show', sale.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View</Link>
                                                        <Link :href="route('sales.edit', sale.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</Link>
                                                        <button @click="deleteSale(sale.id)" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="sales.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No sales found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col md:flex-row justify-between items-center">
                            <div class="text-sm text-gray-700 dark:text-gray-400 mb-4 md:mb-0">
                                Showing {{ (sales.current_page - 1) * sales.per_page + 1 }} to {{ Math.min(sales.current_page * sales.per_page, sales.total) }} of {{ sales.total }} entries
                            </div>
                            <div v-if="sales.links.length > 3" class="flex justify-center">
                                <template v-for="(link, key) in sales.links" :key="key">
                                    <div v-if="link.url === null" class="mr-1 px-3 py-1 text-sm leading-4 text-gray-400 border rounded bg-white" v-html="link.label" />
                                    <Link v-else :class="{ 'bg-blue-600 text-white border-blue-600': link.active, 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active }" class="mr-1 px-3 py-1 text-sm leading-4 border rounded shadow-sm" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
