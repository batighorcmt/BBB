<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';

const props = defineProps({
    sale: Object,
});

const printInvoice = () => {
    window.print();
};

const paymentStatus = computed(() => {
    const due = parseFloat(props.sale.due_amount);
    const paid = parseFloat(props.sale.paid_amount);
    const total = parseFloat(props.sale.grand_total);

    if (due <= 0) return 'Paid';
    if (paid <= 0) return 'Due';
    return 'Partial';
});

const getStatusClass = (status) => {
    switch (status) {
        case 'Paid': return 'bg-green-100 text-green-800';
        case 'Due': return 'bg-red-100 text-red-800';
        case 'Partial': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print')) {
        setTimeout(() => {
            window.print();
        }, 500);
    }
});
</script>

<template>
    <Head :title="`Invoice #${sale.sale_no}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center print:hidden">
                <div class="flex items-center space-x-3">
                    <Link :href="route('sales.index')" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sales Invoice Details</h2>
                </div>
                <div class="flex space-x-3">
                    <button @click="printInvoice" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-md active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Invoice
                    </button>
                    <button @click="printInvoice" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-all shadow-md active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h1.5m1.5 0H13m-4 4h4m-4 4h4"/></svg>
                        Download PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen print:bg-white print:py-0">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 print:w-full print:px-0 print:max-w-none">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg print:shadow-none print:rounded-none p-6 print:p-4">
                    
                    <!-- Simple Header - Compact -->
                    <div class="flex justify-between items-start border-b pb-4 mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">INVOICE</h1>
                            <p class="text-gray-500 font-bold text-lg">#{{ sale.sale_no }}</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold text-2xl text-blue-600">BATIGHOR</h3>
                            <p class="text-gray-500 font-medium text-sm">Computers & IT</p>
                            <div class="text-xs mt-1 text-gray-600 dark:text-gray-400">
                                <p>Santiha, Bogura Sadar, Bogura</p>
                                <p>Phone: +880 1812 345678</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Grid - Compact -->
                    <div class="grid grid-cols-2 gap-8 mb-6">
                        <div>
                            <h4 class="font-bold text-gray-400 uppercase text-[10px] tracking-widest mb-1">Bill To:</h4>
                            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ sale.customer?.name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ sale.customer?.address || 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ sale.customer?.phone }}</p>
                        </div>
                        <div class="text-right text-sm">
                            <div class="mb-1">
                                <span class="font-bold text-gray-400 uppercase text-[10px] tracking-widest">Date:</span>
                                <span class="ml-2 font-bold">{{ new Date(sale.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                            </div>
                            <div class="mb-1">
                                <span class="font-bold text-gray-400 uppercase text-[10px] tracking-widest">Sale Type:</span>
                                <span class="ml-2 font-bold capitalize">{{ sale.type }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-400 uppercase text-[10px] tracking-widest">Status:</span>
                                <span :class="['ml-2 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase', getStatusClass(paymentStatus)]">
                                    {{ paymentStatus }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table - Compact -->
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-gray-100 dark:border-gray-700">
                                    <th class="py-2 font-bold text-gray-900 dark:text-white uppercase text-xs">Item & Details</th>
                                    <template v-if="sale.type === 'wholesale'">
                                        <th class="py-2 font-bold text-gray-900 dark:text-white uppercase text-xs text-center">Specifications</th>
                                        <th class="py-2 font-bold text-gray-900 dark:text-white uppercase text-xs text-center">Add. Costs</th>
                                    </template>
                                    <th class="py-2 font-bold text-gray-900 dark:text-white uppercase text-xs text-center">Qty</th>
                                    <th class="py-2 font-bold text-gray-900 dark:text-white uppercase text-xs text-right">Price</th>
                                    <th class="py-2 font-bold text-gray-900 dark:text-white uppercase text-xs text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="item in sale.items" :key="item.id">
                                    <td class="py-2">
                                        <p class="font-bold text-gray-900 dark:text-white text-sm">{{ item.item_name }}</p>
                                        <p class="text-[10px] text-gray-500">
                                            Size: {{ item.size }} | Color: {{ item.color }} 
                                            <span v-if="item.gsm" class="ml-2 font-bold text-blue-600">| GSM: {{ item.gsm }}</span>
                                        </p>
                                    </td>
                                    <template v-if="sale.type === 'wholesale'">
                                        <td class="py-2 text-center text-[10px] text-gray-600 dark:text-gray-400 leading-tight">
                                            <p><span class="font-bold text-gray-400">Env:</span> {{ item.envelope_weight }}g | ৳{{ item.envelope_price }}</p>
                                            <p><span class="font-bold text-gray-400">Loop:</span> {{ item.loop_weight }}g | ৳{{ item.loop_price }}</p>
                                        </td>
                                        <td class="py-2 text-center text-[10px] text-gray-600 dark:text-gray-400 leading-tight">
                                            <p>Print: ৳{{ item.print_cost }}</p>
                                            <p>Sew: ৳{{ item.sewing_cost }}</p>
                                        </td>
                                    </template>
                                    <td class="py-2 text-center font-bold text-sm">{{ item.quantity }}</td>
                                    <td class="py-2 text-right font-medium text-sm">৳{{ item.price_per_piece }}</td>
                                    <td class="py-2 text-right font-bold text-base">৳{{ item.total_price }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals - Compact -->
                    <div class="flex justify-end pt-4 border-t dark:border-gray-700">
                        <div class="w-full sm:w-1/2 lg:w-1/3 space-y-1 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal:</span>
                                <span>৳{{ sale.subtotal }}</span>
                            </div>
                            <div v-if="parseFloat(sale.discount) > 0" class="flex justify-between text-red-600 text-xs">
                                <span>Discount:</span>
                                <span>-৳{{ sale.discount }}</span>
                            </div>
                            <div v-if="parseFloat(sale.tax) > 0" class="flex justify-between text-blue-600 text-xs">
                                <span>Tax:</span>
                                <span>+৳{{ sale.tax }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 dark:text-white pt-1 border-t">
                                <span class="uppercase text-[10px] tracking-widest self-center text-gray-400">Grand Total:</span>
                                <span class="text-lg">৳{{ sale.grand_total }}</span>
                            </div>
                            <div class="flex justify-between text-green-600 text-[10px] italic">
                                <span>Adv. Adjusted:</span>
                                <span>-৳{{ sale.advance_adjusted }}</span>
                            </div>
                            <div class="flex justify-between text-blue-600 text-[10px] italic">
                                <span>Total Paid:</span>
                                <span>-৳{{ sale.paid_amount }}</span>
                            </div>
                            <div class="flex justify-between text-xl font-bold text-red-600 pt-1 border-t">
                                <span>Balance:</span>
                                <span>৳{{ sale.due_amount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer - Compact -->
                    <div class="mt-8 pt-4 border-t dark:border-gray-700 text-center">
                        <p class="text-[10px] text-gray-500 italic">"Thank you for your business."</p>
                        <p class="text-[8px] text-gray-400 uppercase tracking-widest mt-2 italic">System Generated - Batighor Computers</p>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print\:hidden {
        display: none !important;
    }
    /* Maximize print area */
    @page {
        margin: 1cm;
    }
}
</style>
