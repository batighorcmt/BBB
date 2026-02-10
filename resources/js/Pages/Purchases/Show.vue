<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    purchase: Object,
});
</script>

<template>
    <Head title="View Purchase" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Purchase Details</h2>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 text-gray-900 dark:text-gray-100">
                        <!-- Invoice Header -->
                        <div class="flex justify-between items-center mb-8 border-b pb-4">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-700 dark:text-gray-200">PURCHASE</h1>
                                <p class="text-sm text-gray-500">#{{ purchase.purchase_no }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold">Date: {{ purchase.purchase_date }}</p>
                                <p class="text-sm text-gray-500">Ref: {{ purchase.reference_no || 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Supplier Info -->
                        <div class="mb-8">
                            <h3 class="text-gray-600 uppercase tracking-wide text-xs font-bold mb-2">Supplier:</h3>
                            <p class="font-semibold text-lg">{{ purchase.supplier?.name }}</p>
                            <p class="text-gray-500">{{ purchase.supplier?.phone }}</p>
                            <p class="text-gray-500">{{ purchase.supplier?.address }}</p>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-8">
                            <table class="min-w-full border-collapse">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="py-2 px-4 text-left border-b">Item</th>
                                        <th class="py-2 px-4 text-right border-b">Quantity</th>
                                        <th class="py-2 px-4 text-right border-b">Unit Price</th>
                                        <th class="py-2 px-4 text-right border-b">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in purchase.items" :key="item.id">
                                        <td class="py-2 px-4 border-b">{{ item.item?.name }}</td>
                                        <td class="py-2 px-4 border-b text-right">{{ item.quantity }}</td>
                                        <td class="py-2 px-4 border-b text-right">{{ item.unit_price }}</td>
                                        <td class="py-2 px-4 border-b text-right">{{ item.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="flex justify-end mb-8">
                            <div class="w-1/2 md:w-1/3">
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-semibold">Subtotal:</span>
                                    <span>{{ purchase.subtotal }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-semibold">Other Charges:</span>
                                    <span>{{ purchase.other_charges }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-semibold">Discount:</span>
                                    <span>-{{ purchase.discount }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b text-xl font-bold">
                                    <span>Grand Total:</span>
                                    <span>{{ purchase.total }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b text-green-600">
                                    <span class="font-semibold">Paid:</span>
                                    <span>{{ purchase.paid }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b text-red-600">
                                    <span class="font-semibold">Due:</span>
                                    <span>{{ purchase.due }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-4 no-print">
                            <Link :href="route('purchases.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Back to List
                            </Link>
                            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
