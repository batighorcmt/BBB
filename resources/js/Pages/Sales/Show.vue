<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sale: Object,
});

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Invoice #${sale.sale_no}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center print:hidden">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sale Details</h2>
                <div class="space-x-2">
                    <Link :href="route('sales.index')" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Back</Link>
                    <button @click="printInvoice" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Print Invoice</button>
                </div>
            </div>
        </template>

        <div class="py-12 print:py-0">
            <div class="w-full mx-auto sm:px-6 lg:px-8 print:w-full print:px-0">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg print:shadow-none">
                    <div class="p-8 text-gray-900 dark:text-gray-100 print:text-black">
                        
                        <!-- Invoice Header -->
                        <div class="flex justify-between items-start border-b pb-6 mb-6">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-800 dark:text-white print:text-black">INVOICE</h1>
                                <p class="text-gray-500">#{{ sale.sale_no }}</p>
                            </div>
                            <div class="text-right">
                                <h3 class="font-bold text-lg">Batighor Computers</h3>
                                <p class="text-sm">Dhaka, Bangladesh</p>
                                <p class="text-sm">Phone: +880 1234 567890</p>
                            </div>
                        </div>

                        <!-- Customer & Date Info -->
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div>
                                <h4 class="font-bold text-gray-600 uppercase text-sm mb-2">Bill To:</h4>
                                <p class="font-bold text-lg">{{ sale.customer?.name }}</p>
                                <p class="text-sm">{{ sale.customer?.company_name }}</p>
                                <p class="text-sm">{{ sale.customer?.phone }}</p>
                                <p class="text-sm">{{ sale.customer?.address }}</p>
                            </div>
                            <div class="text-right">
                                <div class="mb-2">
                                    <span class="font-bold text-gray-600 uppercase text-sm">Date:</span>
                                    <span class="ml-2">{{ new Date(sale.created_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="font-bold text-gray-600 uppercase text-sm">Type:</span>
                                    <span class="ml-2 capitalize">{{ sale.type }}</span>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-600 uppercase text-sm">Status:</span>
                                    <span class="ml-2 capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ sale.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-hidden border rounded-lg mb-8">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 print:bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desc (Size/GSM)</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rate</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="item in sale.items" :key="item.id">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ item.item_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            Size: {{ item.size }}, GSM: {{ item.gsm }}, Color: {{ item.color }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold">{{ item.quantity }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ item.price_per_piece }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-bold">{{ item.total_price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Summary -->
                        <div class="flex justify-end">
                            <div class="w-full sm:w-1/2 lg:w-1/3">
                                <div class="space-y-2 text-right">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-bold">{{ sale.subtotal }}</span>
                                    </div>
                                    <div class="flex justify-between" v-if="parseFloat(sale.discount) > 0">
                                        <span class="text-gray-600">Discount:</span>
                                        <span>-{{ sale.discount }}</span>
                                    </div>
                                    <div class="flex justify-between" v-if="parseFloat(sale.tax) > 0">
                                        <span class="text-gray-600">Tax:</span>
                                        <span>+{{ sale.tax }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 font-bold text-lg">
                                        <span>Grand Total:</span>
                                        <span>{{ sale.grand_total }}</span>
                                    </div>
                                    <div class="flex justify-between text-green-600">
                                        <span>Advance Adjusted:</span>
                                        <span>-{{ sale.advance_adjusted }}</span>
                                    </div>
                                    <div class="flex justify-between text-blue-600">
                                        <span>Paid:</span>
                                        <span>-{{ sale.paid_amount }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 font-bold text-xl text-red-600">
                                        <span>Due Balance:</span>
                                        <span>{{ sale.due_amount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Notes -->
                        <div class="mt-12 pt-4 border-t text-sm text-gray-500" v-if="sale.note">
                            <h4 class="font-bold mb-1">Notes:</h4>
                            <p>{{ sale.note }}</p>
                        </div>
                        
                        <div class="mt-8 text-center text-xs text-gray-400 print:mt-16">
                            <p>This is a system generated invoice.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
