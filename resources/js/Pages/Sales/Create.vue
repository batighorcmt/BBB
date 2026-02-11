<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    productions: Array,
});

const form = useForm({
    production_id: '',
    customer_id: '',
    customer_name: '', // For display
    type: 'wholesale',
    sale_date: new Date().toISOString().slice(0, 10),
    items: [],
    subtotal: 0,
    discount: 0,
    tax: 0,
    other_costs: 0,
    grand_total: 0,
    advance_adjusted: 0,
    paid_amount: 0,
    due_amount: 0,
    note: '',
});

const isLoading = ref(false);

const onProductionSelect = async () => {
    if (!form.production_id) return;

    isLoading.value = true;
    try {
        const response = await axios.get(route('api.productions.show', form.production_id));
        const production = response.data;
        
        form.customer_id = production.quotation?.customer_id;
        form.customer_name = production.quotation?.customer?.name;
        form.advance_adjusted = parseFloat(production.advance_amount) || 0;

        // Populate items
        form.items = production.items.map(item => {
            const qItem = item.quotation_item;
            const refItem = qItem?.item; 
            
            // Per-piece price from Quotation if local sale
            const basePrice = form.type === 'local' ? (parseFloat(qItem?.unit_price) || 0) : 0;
            
            return {
                production_item_id: item.id,
                item_name: item.product_name,
                // Fetch specs from Quotation as per requirement
                size: qItem?.size || item.size || '', 
                gsm: qItem?.gsm || refItem?.gsm || '',
                color: qItem?.color || refItem?.fabric_color || '',
                
                // Costs from Production (as defaults/wholesale)
                envelope_weight: parseFloat(item.envelope_weight) || 0,
                envelope_price: parseFloat(item.envelope_price) || 0,
                loop_weight: parseFloat(item.loop_weight) || 0,
                loop_price: parseFloat(item.loop_price) || 0,
                print_cost: parseFloat(item.print_cost) || 0,
                sewing_cost: parseFloat(item.sewing_cost) || 0,
                
                // Quantity from Production
                quantity: parseFloat(item.quantity) || 0,
                
                price_per_piece: basePrice,
                total_price: 0,
            };
        });

        // Initial Calculation
        form.items.forEach((item, index) => calculateRow(index));

    } catch (error) {
        console.error("Error fetching production:", error);
        Swal.fire('Error', 'Failed to load production details.', 'error');
    } finally {
        isLoading.value = false;
    }
};

const calculateRow = (index) => {
    const item = form.items[index];
    const qty = parseFloat(item.quantity) || 0;
    
    if (qty <= 0) {
        item.price_per_piece = 0;
        item.total_price = 0;
        calculateTotals();
        return;
    }

    if (form.type === 'local') {
        // For local sales, price_per_piece is already set from quotation or manual input
        item.total_price = (parseFloat(item.price_per_piece) * qty).toFixed(2);
    } else {
        // Wholesale: Formula based calculation
        const envCost = (parseFloat(item.envelope_weight) || 0) * (parseFloat(item.envelope_price) || 0);
        const loopCost = (parseFloat(item.loop_weight) || 0) * (parseFloat(item.loop_price) || 0);
        const printCostTotal = (parseFloat(item.print_cost) || 0) * qty;
        const sewingCostTotal = (parseFloat(item.sewing_cost) || 0) * qty;
        
        const totalItemCost = envCost + loopCost + printCostTotal + sewingCostTotal;
        
        item.price_per_piece = (totalItemCost / qty).toFixed(2);
        item.total_price = totalItemCost.toFixed(2);
    }
    
    calculateTotals();
};

const calculateTotals = () => {
    let sub = 0;
    form.items.forEach(item => {
        sub += parseFloat(item.total_price) || 0;
    });
    form.subtotal = sub.toFixed(2);
    
    // Grand Total Logic
    const discount = parseFloat(form.discount) || 0;
    const tax = parseFloat(form.tax) || 0;
    const other = parseFloat(form.other_costs) || 0;
    
    const grand = sub - discount + tax + other;
    form.grand_total = grand.toFixed(2);
    
    const advance = parseFloat(form.advance_adjusted) || 0;
    const paid = parseFloat(form.paid_amount) || 0;
    
    // Due = (Grand - Advance) - Paid
    // Note: User said "Receivable" is Grand - Advance.
    form.due_amount = (grand - advance - paid).toFixed(2);
};

// Watchers for financial inputs and type
watch(() => [form.discount, form.tax, form.other_costs, form.advance_adjusted, form.paid_amount], () => {
    calculateTotals();
});

watch(() => form.type, () => {
    // If type changes, we should probably re-populate or at least re-calculate with new logic
    if (form.production_id) {
        onProductionSelect(); 
    }
});

const submit = () => {
    form.post(route('sales.store'), {
        onError: (errors) => {
            console.error(errors);
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please check the form for errors.',
                footer: Object.values(errors).join('<br>')
            });
        }
    });
};
</script>

<template>
    <Head title="Create Sale" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Sale Invoice</h2>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            
                            <!-- Header Selection -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Production <span class="text-red-500">*</span></label>
                                    <select v-model="form.production_id" @change="onProductionSelect" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                        <option value="" disabled>Select Completed Production</option>
                                        <option v-for="p in productions" :key="p.id" :value="p.id">
                                            {{ p.production_no }} - {{ p.quotation?.customer?.name }} (Qty: {{ p.items?.reduce((sum, i) => sum + parseFloat(i.quantity), 0) }})
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sale Type</label>
                                    <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="wholesale">Wholesale</option>
                                        <option value="local">Local Sales</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                                    <input type="text" :value="form.customer_name" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-600 dark:text-gray-300 cursor-not-allowed">
                                </div>
                            </div>

                            <div v-if="isLoading" class="text-center py-8">
                                <p class="text-lg animate-pulse">Loading Production Details...</p>
                            </div>

                            <!-- Items Table -->
                            <div v-if="form.items.length > 0" class="overflow-x-auto mb-8">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Sale Items</h3>
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border">
                                    <thead class="bg-indigo-600 text-white">
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium">Item Name</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium w-16">Size</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium w-16">GSM</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium w-20">Color</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium">Env Wt</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium">Env Price</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium">Loop Wt</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium">Loop Price</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium">Print</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium">Sewing</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium w-16">Qty</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium bg-gray-700">Rate</th>
                                            <th class="px-2 py-2 text-right text-xs font-medium bg-gray-700">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-2 py-2">
                                                <input v-model="item.item_name" type="text" class="w-24 rounded-md border-gray-300 text-xs p-1" readonly>
                                            </td>
                                            <td class="px-2 py-2 text-xs">{{ item.size }}</td>
                                            <td class="px-2 py-2 text-xs text-gray-500">{{ item.gsm }}</td>
                                            <td class="px-2 py-2 text-xs text-gray-500">{{ item.color }}</td>
                                            
                                            <!-- Inputs for recalculation -->
                                            <td class="px-2 py-2"><input v-model="item.envelope_weight" type="number" step="0.001" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.envelope_price" type="number" step="0.01" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.loop_weight" type="number" step="0.001" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.loop_price" type="number" step="0.01" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.print_cost" type="number" step="0.01" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            <td class="px-2 py-2"><input v-model="item.sewing_cost" type="number" step="0.01" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right" @input="calculateRow(index)"></td>
                                            
                                            <td class="px-2 py-2"><input v-model="item.quantity" type="number" step="1" class="w-16 rounded-md border-gray-300 text-xs p-1 text-right font-bold" @input="calculateRow(index)"></td>
                                            
                                            <td class="px-2 py-2 text-right text-xs font-bold bg-gray-50">{{ item.price_per_piece }}</td>
                                            <td class="px-2 py-2 text-right text-xs font-bold bg-gray-50">{{ item.total_price }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Financial Footer -->
                            <div v-if="form.items.length > 0" class="flex flex-col md:flex-row justify-end space-y-4 md:space-y-0 md:space-x-8">
                                <div class="w-full md:w-1/3 space-y-3">
                                    <label class="block text-sm font-medium">Notes</label>
                                    <textarea v-model="form.note" class="w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea>
                                </div>
                                
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-lg w-full md:w-1/2 lg:w-1/3">
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                            <span class="font-bold">{{ form.subtotal }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 dark:text-gray-400">Less: Discount</span>
                                            <input v-model="form.discount" type="number" class="w-24 text-right rounded-md border-gray-300 p-1">
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 dark:text-gray-400">Add: Tax/VAT</span>
                                            <input v-model="form.tax" type="number" class="w-24 text-right rounded-md border-gray-300 p-1">
                                        </div>
                                        <div class="flex justify-between items-center border-b pb-2">
                                            <span class="text-gray-600 dark:text-gray-400">Other Costs</span>
                                            <input v-model="form.other_costs" type="number" class="w-24 text-right rounded-md border-gray-300 p-1">
                                        </div>
                                        
                                        <div class="flex justify-between items-center pt-2">
                                            <span class="text-lg font-bold">Grand Total</span>
                                            <span class="text-lg font-bold">{{ form.grand_total }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center text-green-700">
                                            <span>Less: Advance (Adjusted)</span>
                                            <input v-model="form.advance_adjusted" type="number" class="w-24 text-right rounded-md border-green-300 bg-green-50 p-1 font-bold">
                                        </div>
                                        
                                        <div class="flex justify-between items-center font-semibold border-t pt-2 mt-2">
                                            <span>Receivable Amount</span>
                                            <span>{{ (parseFloat(form.grand_total) - parseFloat(form.advance_adjusted || 0)).toFixed(2) }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center bg-blue-50 p-2 rounded">
                                            <span class="font-bold text-blue-800">Paid Now</span>
                                            <input v-model="form.paid_amount" type="number" class="w-32 text-right rounded-md border-blue-300 p-1 font-bold text-lg">
                                        </div>
                                        
                                        <div class="flex justify-between items-center pt-2">
                                            <span class="text-xl font-bold text-red-600">Due Amount</span>
                                            <span class="text-xl font-bold text-red-600">{{ form.due_amount }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <Link :href="route('sales.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</Link>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded shadow-lg" :disabled="form.processing">
                                    Finalize Sale
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
