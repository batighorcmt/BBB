<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    sales: Object,
});
</script>

<template>
    <Head :title="customer.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Customer Profile</h2>
                <Link :href="route('customers.index')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Back to List</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Customer Details Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-bold mb-4">Basic Information</h3>
                                <div class="space-y-2">
                                    <p><span class="font-medium text-gray-500">Name:</span> {{ customer.name }}</p>
                                    <p><span class="font-medium text-gray-500">Company:</span> {{ customer.company_name || 'N/A' }}</p>
                                    <p><span class="font-medium text-gray-500">Phone:</span> {{ customer.phone }}</p>
                                    <p><span class="font-medium text-gray-500">Email:</span> {{ customer.email || 'N/A' }}</p>
                                    <p><span class="font-medium text-gray-500">Address:</span> {{ customer.address || 'N/A' }}</p>
                                    <p><span class="font-medium text-gray-500">Notes:</span> {{ customer.notes || 'N/A' }}</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-4">Financial Status</h3>
                                <div class="space-y-4">
                                    <div class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                        <p class="text-sm text-blue-600 dark:text-blue-300">Current Balance (Due)</p>
                                        <p class="text-2xl font-bold text-blue-800 dark:text-white">{{ customer.current_balance }}</p>
                                    </div>
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Credit Limit</p>
                                        <p class="text-xl font-bold text-gray-800 dark:text-white">{{ customer.credit_limit }}</p>
                                    </div>
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Opening Balance</p>
                                        <p class="text-xl font-bold text-gray-800 dark:text-white">{{ customer.opening_balance }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales History -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Purchase History (Sales)</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sale No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Paid</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                            <Link :href="route('sales.show', sale.id)">{{ sale.sale_no }}</Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ new Date(sale.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold">{{ sale.grand_total }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">{{ sale.paid_amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-bold">{{ sale.due_amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <Link :href="route('sales.show', sale.id)" class="text-blue-600 hover:text-blue-900">View</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="sales.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No purchase history found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-4 flex justify-between items-center" v-if="sales.last_page > 1">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ sales.from }} to {{ sales.to }} of {{ sales.total }} results
                            </span>
                            <div>
                                <Link v-for="(link, k) in sales.links" :key="k" 
                                    :href="link.url || '#'" 
                                    v-html="link.label"
                                    :class="['px-3 py-1 border rounded text-sm mx-1', link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']"
                                    :preserve-scroll="true"
                                />
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
