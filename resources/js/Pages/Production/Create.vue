<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    quotations: Array,
});

const form = useForm({
    quotation_id: '',
    production_date: new Date().toISOString().substr(0, 10),
    status: 'pending', // Default pending
    items: [],
    total_cost: 0,
    note: '',
});

const selectedQuotation = ref(null);
const isLoading = ref(false);

const onQuotationSelect = async () => {
    if (!form.quotation_id) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(route('api.quotations.show', form.quotation_id));
        selectedQuotation.value = response.data;
        
        // Populate items
        form.items = response.data.items.map(item => ({
            quotation_item_id: item.id,
            product_name: item.item?.name || item.description || 'Unknown Product',
            size: item.size || '', // Pre-fill from quotation if available
            quantity: parseFloat(item.quantity),
            
            // Production Inputs (Initialize to 0)
            envelope_weight: 0,
            envelope_price: 0,
            loop_weight: 0,
            loop_price: 0,
            print_cost: 0,
            sewing_cost: 0,
            
            // Calculated
            price_per_piece: 0,
            total_price: 0,
            
            // Wastage
            wastage_kg: 0,
            wastage_piece: 0,
        }));
        
        calculateTotals();
    } catch (error) {
        console.error("Error fetching quotation:", error);
        alert("Failed to load quotation details.");
    } finally {
        isLoading.value = false;
    }
};

// Calculation Logic
// Per Piece Price = ((EnvWeight * EnvPrice) + (LoopWeight * LoopPrice) + (PrintPrice * Qty) + (SewingCost * Qty)) / Qty
// Wait... (PrintPrice * Qty) implies PrintPrice is 'Rate'. (SewingCost * Qty) implies SewingCost is 'Rate'.
// "Print Cost" usually means total for the batch or per piece? 
// User formula: `(Print Cost * Quantity)` -> This implies the input `Print Cost` is per piece rate.
// User formula: `(Sewing Cost * Quantity)` -> This implies the input `Sewing Cost` is per piece rate.

const calculateRow = (index) => {
    const item = form.items[index];
    const qty = parseFloat(item.quantity) || 0;
    
    if (qty <= 0) {
        item.price_per_piece = 0;
        item.total_price = 0;
        return;
    }

    const envCost = (parseFloat(item.envelope_weight) || 0) * (parseFloat(item.envelope_price) || 0);
    const loopCost = (parseFloat(item.loop_weight) || 0) * (parseFloat(item.loop_price) || 0);
    const printCostTotal = (parseFloat(item.print_cost) || 0) * qty;
    const sewingCostTotal = (parseFloat(item.sewing_cost) || 0) * qty;
    
    const totalItemCost = envCost + loopCost + printCostTotal + sewingCostTotal;
    
    item.price_per_piece = (totalItemCost / qty).toFixed(2);
    item.total_price = totalItemCost.toFixed(2);
    
    calculateTotals();
};

const calculateTotals = () => {
    let sub = 0;
    form.items.forEach(item => {
        sub += parseFloat(item.total_price) || 0;
    });
    form.total_cost = sub.toFixed(2);
};

// Grand Total Calculation
const grandTotalInfo = computed(() => {
    const total = parseFloat(form.total_cost) || 0;
    const advance = parseFloat(selectedQuotation.value?.advance_amount) || 0;
    return {
        total: total.toFixed(2),
        advance: advance.toFixed(2),
        final: (total - advance).toFixed(2)
    };
});

const submit = () => {
    form.post(route('production.store'));
};
</script>

<template>
    <Head title="Start Production" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Start New Production</h2>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            
                            <!-- Header Selection -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Quotation <span class="text-red-500">*</span></label>
                                    <select v-model="form.quotation_id" @change="onQuotationSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                        <option value="" disabled>Select a Pending Quotation</option>
                                        <option v-for="q in quotations" :key="q.id" :value="q.id">{{ q.quotation_no }} - {{ q.customer?.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="pending">Pending</option>
                                        <option value="working">Working</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                                    <input v-model="form.note" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                            </div>

                            <div v-if="isLoading" class="text-center py-4">Loading Quotation Details...</div>

                            <!-- Production Items Table -->
                            <div v-if="form.items.length > 0" class="overflow-x-auto mb-8">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Production Items</h3>
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border">
                                    <thead class="bg-blue-600 text-white">
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Product</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium w-16">Qty</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Env Wt (kg)</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Env Price</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Loop Wt (kg)</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Loop Price</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Print (pc)</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Sewing (pc)</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium bg-gray-700">Cost/Pc</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium bg-gray-700">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-2 py-2 text-sm">{{ item.product_name }}</td>
                                            <td class="px-2 py-2">
                                                <input v-model="item.quantity" type="number" step="1" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)" required>
                                            </td>
                                            
                                            <!-- Cost Inputs -->
                                            <td class="px-2 py-2"><input v-model="item.envelope_weight" type="number" step="0.001" class="w-20 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.envelope_price" type="number" step="0.01" class="w-20 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.loop_weight" type="number" step="0.001" class="w-20 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.loop_price" type="number" step="0.01" class="w-20 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.print_cost" type="number" step="0.01" class="w-20 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.sewing_cost" type="number" step="0.01" class="w-20 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            
                                            <!-- Calculated Results -->
                                            <td class="px-2 py-2 font-bold bg-gray-50 dark:bg-gray-900 text-right text-sm">{{ item.price_per_piece }}</td>
                                            <td class="px-2 py-2 font-bold bg-gray-50 dark:bg-gray-900 text-right text-sm">{{ item.total_price }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Wastage Section (Initially hidden or Separate Table as per user request "Alada Table") -->
                            <div v-if="form.items.length > 0" class="mb-8">
                                <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-2">Wastage Details</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-red-200">
                                        <thead class="bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-100">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium">Product</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium">Wastage (kg)</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium">Wastage (Piece)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                             <tr v-for="(item, index) in form.items" :key="index">
                                                 <td class="px-4 py-2 text-sm">{{ item.product_name }}</td>
                                                 <td class="px-4 py-2"><input v-model="item.wastage_kg" type="number" step="0.001" class="w-32 rounded-md border-red-300 text-sm p-1"></td>
                                                 <td class="px-4 py-2"><input v-model="item.wastage_piece" type="number" step="1" class="w-32 rounded-md border-red-300 text-sm p-1"></td>
                                             </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Footer Totals -->
                            <div v-if="form.items.length > 0" class="flex justify-end">
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-lg w-full md:w-1/3">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600 dark:text-gray-400">Total Production Cost:</span>
                                        <span class="font-bold text-lg">{{ grandTotalInfo.total }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-2 border-b pb-2">
                                        <span class="text-gray-600 dark:text-gray-400">Advance Paid (from Quotation):</span>
                                        <span class="font-bold text-green-600">- {{ grandTotalInfo.advance }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2">
                                        <span class="text-xl font-bold text-gray-800 dark:text-white">Final / Due Amount:</span>
                                        <span class="text-xl font-bold text-indigo-600">{{ grandTotalInfo.final }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <Link :href="route('production.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</Link>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow-lg" :disabled="form.processing">
                                    Save Production
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
