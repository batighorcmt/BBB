<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    quotation: Object,
});

const print = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Quotation ${quotation.quotation_no}`" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex justify-between items-center no-print">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Quotation #{{ quotation.quotation_no }}
                </h2>
                <div>
                    <button @click="print" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-2 hover:bg-gray-300">Print</button>
                    <Link :href="route('quotations.index')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Back</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" id="printable-area">
                    <div class="p-8 text-gray-900 dark:text-gray-100">
                        
                        <!-- Header -->
                        <div class="flex justify-between mb-8 border-b pb-4">
                            <div>
                                <h1 class="text-2xl font-bold">QUOTATION</h1>
                                <p class="text-sm text-gray-500">Date: {{ quotation.quotation_date }}</p>
                                <p class="text-sm text-gray-500" v-if="quotation.valid_until">Valid Until: {{ quotation.valid_until }}</p>
                            </div>
                            <div class="text-right">
                                <h3 class="text-lg font-bold">Customer Info</h3>
                                <p>{{ quotation.customer?.name }}</p>
                                <p>{{ quotation.customer?.address }}</p>
                                <p>{{ quotation.customer?.phone }}</p>
                            </div>
                        </div>

                        <!-- Items -->
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mb-6">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Specs</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in quotation.items" :key="item.id">
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{{ item.item?.name }}</div>
                                        <div class="text-xs text-gray-500">{{ item.description }}</div>
                                    </td>
                                    <td class="px-4 py-2 text-xs">
                                        <div v-if="item.size">Size: {{ item.size }}</div>
                                        <div v-if="item.color">Color: {{ item.color }}</div>
                                        <div v-if="item.gsm">GSM: {{ item.gsm }}</div>
                                    </td>
                                    <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                    <td class="px-4 py-2 text-right">{{ item.unit_price }}</td>
                                    <td class="px-4 py-2 text-right font-medium">{{ item.total }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Totals -->
                         <div class="flex justify-end">
                            <div class="w-1/2 md:w-1/3">
                                <div class="flex justify-between py-1 border-t">
                                    <span class="font-medium">Subtotal:</span>
                                    <span>{{ quotation.subtotal }}</span>
                                </div>
                                <div class="flex justify-between py-1" v-if="parseFloat(quotation.discount) > 0">
                                    <span class="text-sm">Discount:</span>
                                    <span>-{{ quotation.discount }}</span>
                                </div>
                                 <div class="flex justify-between py-1" v-if="parseFloat(quotation.other_charges) > 0">
                                    <span class="text-sm">Other Charges:</span>
                                    <span>+{{ quotation.other_charges }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-t border-b font-bold text-lg">
                                    <span>Grand Total:</span>
                                    <span>{{ quotation.total }}</span>
                                </div>
                                <div class="flex justify-between py-2 text-green-700 font-medium" v-if="parseFloat(quotation.advance_amount) > 0">
                                    <span>Advance Paid:</span>
                                    <span>{{ quotation.advance_amount }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-8 pt-4 border-t text-sm text-gray-500">
                            <div v-if="quotation.terms_conditions">
                                <h4 class="font-medium text-gray-700 mb-1">Terms & Conditions:</h4>
                                <p class="whitespace-pre-line">{{ quotation.terms_conditions }}</p>
                            </div>
                            <div v-if="quotation.notes" class="mt-4">
                                <h4 class="font-medium text-gray-700 mb-1">Notes:</h4>
                                <p class="whitespace-pre-line">{{ quotation.notes }}</p>
                            </div>
                            
                            <div class="mt-8 text-center text-xs">
                                <p>Created by: {{ quotation.creator?.name }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none;
    }
    body {
        background: white;
    }
    .shadow-sm, .shadow-lg {
        box-shadow: none !important;
    }
}
</style>
