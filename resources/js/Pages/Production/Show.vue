<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    production: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'working': return 'bg-blue-100 text-blue-800';
        case 'completed': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="View Production" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Production #{{ production.production_no }}</h2>
                <Link :href="route('production.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Header Details -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <span class="block text-sm font-bold text-gray-500">Quotation No</span>
                                <span class="text-lg">{{ production.quotation?.quotation_no }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-500">Customer</span>
                                <span class="text-lg">{{ production.quotation?.customer?.name }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-500">Status</span>
                                <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColor(production.status)]">
                                    {{ production.status }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-500">Created By</span>
                                <span class="text-lg">{{ production.creator?.name }}</span>
                            </div>
                            <div class="md:col-span-4 mt-2 border-t pt-2" v-if="production.note">
                                <span class="block text-sm font-bold text-gray-500">Note</span>
                                <p class="text-gray-700 dark:text-gray-300">{{ production.note }}</p>
                            </div>
                        </div>

                        <!-- Production Items Table -->
                        <div class="overflow-x-auto mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Production Items</h3>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border">
                                <thead class="bg-blue-600 text-white">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium">Product</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium">Size</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium w-16">Qty</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium">Env Wt</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium">Env Price</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium">Loop Wt</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium">Loop Price</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium">Print</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium">Sewing</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium bg-gray-700">Cost/Pc</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium bg-gray-700">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                    <tr v-for="item in production.items" :key="item.id">
                                        <td class="px-2 py-2 text-sm">{{ item.product_name }}</td>
                                        <td class="px-2 py-2 text-sm">{{ item.size }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.quantity }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.envelope_weight }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.envelope_price }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.loop_weight }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.loop_price }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.print_cost }}</td>
                                        <td class="px-2 py-2 text-right text-sm">{{ item.sewing_cost }}</td>
                                        <td class="px-2 py-2 text-right text-sm font-bold">{{ item.price_per_piece }}</td>
                                        <td class="px-2 py-2 text-right text-sm font-bold bg-gray-50 dark:bg-gray-900">{{ item.total_price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                         <!-- Wastage Section -->
                         <div class="mb-8" v-if="production.items.some(i => i.wastage_kg > 0 || i.wastage_piece > 0)">
                            <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-2">Wastage Details</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-red-200">
                                    <thead class="bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium">Product</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium">Wastage (kg)</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium">Wastage (Piece)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                         <tr v-for="item in production.items" :key="item.id">
                                             <td class="px-4 py-2 text-sm">{{ item.product_name }}</td>
                                             <td class="px-4 py-2 text-right text-sm">{{ item.wastage_kg }}</td>
                                             <td class="px-4 py-2 text-right text-sm">{{ item.wastage_piece }}</td>
                                         </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Footer Totals -->
                        <div class="flex justify-end">
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-lg w-full md:w-1/3">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600 dark:text-gray-400">Total Production Cost:</span>
                                    <span class="font-bold text-lg">{{ production.total_cost }}</span>
                                </div>
                                <div class="flex justify-between items-center mb-2 border-b pb-2">
                                    <span class="text-gray-600 dark:text-gray-400">Advance Paid:</span>
                                    <span class="font-bold text-green-600">- {{ production.advance_amount }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-xl font-bold text-gray-800 dark:text-white">Final / Due Amount:</span>
                                    <span class="text-xl font-bold text-indigo-600">{{ production.final_cost }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
