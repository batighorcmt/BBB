<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    quotation: Object,
    customers: Array,
    items: Array,
});

const page = usePage();
const user = page.props.auth.user;
const isCustomer = user.roles.some(r => r.name === 'customer');

const form = useForm({
    customer_id: props.quotation.customer_id,
    quotation_date: props.quotation.quotation_date,
    valid_until: props.quotation.valid_until || '',
    items: props.quotation.items.map(item => ({
        item_id: item.item_id,
        description: item.description, // Keep original or fetch new? Usually keep original unless item changes
        size: item.size || '',
        color: item.color || '',
        gsm: item.gsm || '',
        print_color: item.print_color || '',
        print_side: item.print_side || '',
        quantity: parseFloat(item.quantity),
        unit_price: parseFloat(item.unit_price),
        total: parseFloat(item.total).toFixed(2)
    })),
    subtotal: props.quotation.subtotal,
    discount: props.quotation.discount,
    other_charges: props.quotation.other_charges || 0, // Ensure db has this column or use 0
    advance_amount: props.quotation.advance_amount,
    grand_total: props.quotation.total,
    notes: props.quotation.notes || '',
    terms_conditions: props.quotation.terms_conditions || '',
});

const addItem = () => {
    form.items.push({ item_id: '', description: '', size: '', color: '', gsm: '', print_color: '', print_side: '', quantity: 1, unit_price: 0, total: 0 });
};

import Swal from 'sweetalert2';

const removeItem = (index) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
             form.items.splice(index, 1);
             calculateTotals();
             Swal.fire(
                'Removed!',
                'Item has been removed.',
                'success'
            )
        }
    });
};

const onItemSelected = (index) => {
    const itemId = form.items[index].item_id;
    const selectedItem = props.items.find(i => i.id === itemId);
    if (selectedItem) {
        form.items[index].description = selectedItem.name;
        form.items[index].unit_price = selectedItem.price;
        form.items[index].size = selectedItem.size || '';
        form.items[index].color = selectedItem.fabric_color || '';
        form.items[index].gsm = selectedItem.gsm || '';
        form.items[index].print_color = selectedItem.print_color_count || '';
        form.items[index].print_side = selectedItem.print_sides || '';
        
        calculateRowTotal(index);
    }
};

const calculateRowTotal = (index) => {
    const item = form.items[index];
    item.total = (item.quantity * item.unit_price).toFixed(2);
    calculateTotals();
};

const calculateTotals = () => {
    let sub = 0;
    form.items.forEach(item => {
        sub += parseFloat(item.total) || 0;
    });
    form.subtotal = sub.toFixed(2);
    
    // Grand Total
    const other = parseFloat(form.other_charges) || 0;
    const discount = parseFloat(form.discount) || 0;
    form.grand_total = (sub + other - discount).toFixed(2);
};

// Deep watch items
watch(() => form.items, (newVal) => {
    newVal.forEach((item, index) => {
         const total = (item.quantity * item.unit_price).toFixed(2);
         if (total !== item.total) {
            item.total = total;
            calculateTotals();
         }
    });
    calculateTotals();
}, { deep: true });

watch(() => [form.other_charges, form.discount], () => {
    calculateTotals();
});

const submit = () => {
    form.put(route('quotations.update', props.quotation.id));
};
</script>

<template>
    <Head title="Edit Quotation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Quotation #{{ quotation.quotation_no }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Header Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <InputLabel for="customer_id" value="Customer" />
                                    <!-- Readonly for everyone in Edit mode usually, or editable if admin? Let's allow edit if admin -->
                                    <select v-if="!isCustomer" id="customer_id" v-model="form.customer_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="" disabled>Select Customer</option>
                                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }} ({{ customer.phone }})</option>
                                    </select>
                                    <div v-else class="mt-2 font-medium">{{ quotation.customer?.name }}</div>
                                    <div v-if="form.errors.customer_id" class="text-red-500 text-xs mt-1">{{ form.errors.customer_id }}</div>
                                </div>
                                
                                <div>
                                    <InputLabel for="quotation_date" value="Date" />
                                    <TextInput id="quotation_date" type="date" class="mt-1 block w-full" v-model="form.quotation_date" required />
                                    <div v-if="form.errors.quotation_date" class="text-red-500 text-xs mt-1">{{ form.errors.quotation_date }}</div>
                                </div>
                                
                                <div>
                                    <InputLabel for="valid_until" value="Valid Until" />
                                    <TextInput id="valid_until" type="date" class="mt-1 block w-full" v-model="form.valid_until" />
                                    <div v-if="form.errors.valid_until" class="text-red-500 text-xs mt-1">{{ form.errors.valid_until }}</div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="overflow-x-auto mb-6">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">GSM</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Print Color</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase text-right">Qty</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase text-right">Rate</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase text-right">Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-2 py-2 min-w-[200px]">
                                                 <select v-model="item.item_id" @change="onItemSelected(index)" class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm" required>
                                                    <option value="" disabled>Select Item</option>
                                                    <option v-for="i in items" :key="i.id" :value="i.id">{{ i.name }} - {{ i.code }}</option>
                                                </select>
                                            </td>
                                            <td class="px-2 py-2"><input v-model="item.size" type="text" class="w-20 border-gray-300 rounded-md text-sm p-1" ></td>
                                            <td class="px-2 py-2"><input v-model="item.color" type="text" class="w-20 border-gray-300 rounded-md text-sm p-1" ></td>
                                            <td class="px-2 py-2"><input v-model="item.gsm" type="text" class="w-16 border-gray-300 rounded-md text-sm p-1" ></td>
                                            <td class="px-2 py-2"><input v-model="item.print_color" type="text" class="w-20 border-gray-300 rounded-md text-sm p-1" ></td>
                                            <td class="px-2 py-2"><input v-model="item.quantity" type="number" min="1" class="w-20 border-gray-300 rounded-md text-sm p-1 text-right" required @input="calculateRowTotal(index)"></td>
                                            <td class="px-2 py-2">
                                                <input v-model="item.unit_price" type="number" step="0.01" min="0" class="w-24 border-gray-300 rounded-md text-sm p-1 text-right" :readonly="isCustomer" @input="calculateRowTotal(index)" required>
                                            </td>
                                            <td class="px-2 py-2 text-right font-semibold">{{ item.total }}</td>
                                            <td class="px-2 py-2">
                                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700" v-if="form.items.length > 1">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" @click="addItem" class="mt-2 text-indigo-600 hover:text-indigo-900 text-sm font-medium flex items-center">
                                    + Add Another Item
                                </button>
                            </div>

                            <!-- Footer Totals -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-4">
                                <div>
                                    <InputLabel for="notes" value="Notes" />
                                    <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-md">
                                    <div class="flex justify-between mb-2">
                                        <span class="font-medium">Subtotal:</span>
                                        <span>{{ form.subtotal }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2 items-center">
                                        <span class="text-sm">Other Charges:</span>
                                        <input v-model="form.other_charges" type="number" step="0.01" class="w-24 border-gray-300 rounded-md text-sm p-1 text-right">
                                    </div>
                                    <div class="flex justify-between mb-2 items-center">
                                        <span class="text-sm">Discount:</span>
                                        <input v-model="form.discount" type="number" step="0.01" class="w-24 border-gray-300 rounded-md text-sm p-1 text-right">
                                    </div>
                                    <div class="flex justify-between mb-4 text-lg font-bold border-t pt-2">
                                        <span>Grand Total:</span>
                                        <span>{{ form.grand_total }}</span>
                                    </div>
                                     <div class="flex justify-between mb-2 items-center">
                                        <span class="text-sm font-medium text-green-700">Advance Payment:</span>
                                        <input v-model="form.advance_amount" type="number" step="0.01" class="w-24 border-green-300 rounded-md text-sm p-1 text-right focus:ring-green-500">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link :href="route('quotations.index')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-2 hover:bg-gray-300">Cancel</Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Quotation
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
