<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    suppliers: Array,
    items: Array,
    paymentMethods: Array,
});

const form = useForm({
    supplier_id: '',
    purchase_date: new Date().toISOString().substr(0, 10),
    reference_no: '',
    items: [],
    discount_type: 'fixed',
    discount: 0,
    other_charges: 0,
    notes: '',
    paid_amount: 0,
    payment_method: 'cash',
    payment_note: '',
});

const addItem = () => {
    form.items.push({
        item_id: '',
        quantity: 1,
        unit_price: 0,
        discount: 0,
        tax: 0,
        total: 0
    });
};

// Initial item
addItem();

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const onItemSelected = (index) => {
    const itemId = form.items[index].item_id;
    const selectedItem = props.items.find(i => i.id === itemId);
    if (selectedItem) {
        form.items[index].unit_price = selectedItem.cost || 0;
        // Optionally set other defaults
    }
    calculateRowTotal(index);
};

const calculateRowTotal = (index) => {
    const item = form.items[index];
    // Simple logic: (Qty * Price) - Discount + Tax = Total
    // Adjust based on business requirement if Discount is per unit or total line
    const baseTotal = (item.quantity || 0) * (item.unit_price || 0);
    // Assuming discount/tax are absolute values for this line for now, or per unit? 
    // Screenshot shows "Discount(tk)" which implies fixed amount.
    // Let's assume line discount is fixed amount for the line.
    
    // item.total = baseTotal - (item.discount || 0) + (item.tax || 0);
    // Actually screenshot shows "Unit Cost" column, maybe calculated?
    // Let's stick to standard: Qty * Price.
    
    form.items[index].total = baseTotal;
};

// Computed Totals
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.total) || 0), 0);
});

const totalQty = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
});

const grandTotal = computed(() => {
    let discount = 0;
    if (form.discount_type === 'fixed') {
        discount = Number(form.discount) || 0;
    } else {
        discount = (subtotal.value * (Number(form.discount) || 0)) / 100;
    }
    return (subtotal.value + (Number(form.other_charges) || 0)) - discount;
});

const dueAmount = computed(() => {
    return grandTotal.value - (Number(form.paid_amount) || 0);
});

const submit = () => {
    form.post(route('purchases.store'), {
        onError: (errors) => {
            console.error(errors);
            alert('Please check the form for errors.');
        }
    });
};
</script>

<template>
    <Head title="Add Purchase" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Add Purchase</h2>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Top Section: Supplier & Date -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Name <span class="text-red-500">*</span></label>
                                    <select v-model="form.supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                        <option value="">Select Supplier</option>
                                        <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                                    </select>
                                    <div v-if="form.errors.supplier_id" class="text-red-500 text-xs mt-1">{{ form.errors.supplier_id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Date <span class="text-red-500">*</span></label>
                                    <input v-model="form.purchase_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                    <div v-if="form.errors.purchase_date" class="text-red-500 text-xs mt-1">{{ form.errors.purchase_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference No</label>
                                    <input v-model="form.reference_no" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                            </div>

                            <!-- Search/Add Item Helper (Optional UI sugar, for now direct row addition) -->
                            <!-- Item Grid -->
                            <div class="mb-8">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border">
                                        <thead class="bg-blue-600 text-white">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-sm font-medium">Item Name</th>
                                                <th class="px-4 py-2 text-left text-sm font-medium w-24">Quantity</th>
                                                <th class="px-4 py-2 text-left text-sm font-medium w-32">Purchase Price</th>
                                                <!-- Optional: Discount/Tax columns per item -->
                                                <th class="px-4 py-2 text-left text-sm font-medium w-32">Total Amount</th>
                                                <th class="px-4 py-2 text-center text-sm font-medium w-16">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                            <tr v-for="(item, index) in form.items" :key="index">
                                                <td class="px-4 py-2">
                                                    <select v-model="item.item_id" @change="onItemSelected(index)" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm" required>
                                                        <option value="">Select Item</option>
                                                        <option v-for="i in props.items" :key="i.id" :value="i.id">{{ i.name }} ({{ i.code }})</option>
                                                    </select>
                                                    <div v-if="form.errors[`items.${index}.item_id`]" class="text-red-500 text-xs mt-1">{{ form.errors[`items.${index}.item_id`] }}</div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input v-model="item.quantity" @input="calculateRowTotal(index)" type="number" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm text-right" required>
                                                    <div v-if="form.errors[`items.${index}.quantity`]" class="text-red-500 text-xs mt-1">{{ form.errors[`items.${index}.quantity`] }}</div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input v-model="item.unit_price" @input="calculateRowTotal(index)" type="number" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm text-right" required>
                                                    <div v-if="form.errors[`items.${index}.unit_price`]" class="text-red-500 text-xs mt-1">{{ form.errors[`items.${index}.unit_price`] }}</div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input v-model="item.total" type="number" step="0.01" class="block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-600 shadow-sm dark:text-white text-sm text-right cursor-not-allowed" disabled>
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                 <td colspan="5" class="px-4 py-2">
                                                    <button type="button" @click="addItem" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm w-full block">
                                                        + Add Another Item
                                                    </button>
                                                 </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div v-if="form.errors.items" class="text-red-500 text-xs mt-1">{{ form.errors.items }}</div>
                            </div>

                            <!-- Calculations Section -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                                    <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Quantities:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ totalQty }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Subtotal:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Other Charges:</span>
                                        <input v-model="form.other_charges" type="number" step="0.01" class="w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right text-sm">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Discount on All:</span>
                                        <div class="flex space-x-2 w-48">
                                            <input v-model="form.discount" type="number" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right text-sm">
                                            <select v-model="form.discount_type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="border-t pt-3 flex justify-between items-center">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">Grand Total:</span>
                                        <span class="text-lg font-bold text-indigo-600">{{ grandTotal.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Make Payment Section -->
                            <div class="mb-8 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-indigo-700 dark:text-indigo-400 mb-4">Make Payment</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                                        <input v-model="form.paid_amount" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Type</label>
                                        <select v-model="form.payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option v-for="method in props.paymentMethods" :key="method" :value="method">{{ method }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Note</label>
                                        <input v-model="form.payment_note" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <Link :href="route('purchases.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Close
                                </Link>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow-lg" :disabled="form.processing">
                                    Save Purchase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
