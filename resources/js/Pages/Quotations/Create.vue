<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';


const props = defineProps({
    customers: Array, // Empty if customer role
    items: Array,
});

const page = usePage();
const user = page.props.auth.user;
const isCustomer = user.roles.some(r => r.name === 'customer');

const form = useForm({
    customer_id: isCustomer ? (user.customer_id || '') : '', // Logic needed to map user to customer_id if isCustomer
    quotation_date: new Date().toISOString().substr(0, 10),
    valid_until: '',
    items: [
        { item_id: '', description: '', size: '', color: '', gsm: '', print_color: '', print_side: '', quantity: 1, unit_price: 0, total: 0 }
    ],
    subtotal: 0,
    discount: 0,
    other_charges: 0,
    advance_amount: 0,
    grand_total: 0,
    notes: '',
    terms_conditions: '',
});

// Searchable Item Logic (Simple implementation, ideally use a library like VueMultiselect)
const filterItems = (query) => {
    if (!query) return props.items;
    return props.items.filter(item => item.name.toLowerCase().includes(query.toLowerCase()));
};

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
        form.items[index].description = selectedItem.name; // Or description
        form.items[index].unit_price = selectedItem.price;
        // Auto-fill specs if available on item
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

// Deep watch items for quantity/price changes
watch(() => form.items, (newVal) => {
    newVal.forEach((item, index) => {
         // Re-calc row if qty/price changed manually
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
    form.post(route('quotations.store'));
};
</script>

<template>
    <Head title="Create Quotation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create Quotation</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Header Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div v-if="!isCustomer">
                                    <InputLabel for="customer_id" value="Customer" />
                                    <v-select 
                                        id="customer_id" 
                                        v-model="form.customer_id" 
                                        :options="props.customers" 
                                        :reduce="customer => customer.id" 
                                        label="name"
                                        placeholder="Select Customer"
                                        class="mt-1 block w-full vue-select-custom"
                                        required
                                    >
                                        <template #option="option">
                                            {{ option.name }} ({{ option.phone }})
                                        </template>
                                    </v-select>
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
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Size</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Color</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">GSM</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Print Color</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Rate</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-2 py-2 min-w-[250px]">
                                                <v-select 
                                                    v-model="item.item_id" 
                                                    :options="props.items" 
                                                    :reduce="i => i.id" 
                                                    label="name"
                                                    placeholder="Select Item"
                                                    @update:modelValue="onItemSelected(index)"
                                                    class="text-sm vue-select-custom"
                                                    required
                                                >
                                                    <template #option="option">
                                                        {{ option.name }} - {{ option.code }}
                                                    </template>
                                                </v-select>
                                            </td>
                                            <td class="px-2 py-2 text-center"><input v-model="item.size" type="text" class="w-20 border-gray-300 rounded-md text-sm p-1 text-center" ></td>
                                            <td class="px-2 py-2 text-center"><input v-model="item.color" type="text" class="w-20 border-gray-300 rounded-md text-sm p-1 text-center" ></td>
                                            <td class="px-2 py-2 text-center"><input v-model="item.gsm" type="text" class="w-16 border-gray-300 rounded-md text-sm p-1 text-center" ></td>
                                            <td class="px-2 py-2 text-center"><input v-model="item.print_color" type="text" class="w-20 border-gray-300 rounded-md text-sm p-1 text-center" ></td>
                                            <td class="px-2 py-2 text-center"><input v-model="item.quantity" type="number" min="1" class="w-20 border-gray-300 rounded-md text-sm p-1 text-center" required @input="calculateRowTotal(index)"></td>
                                            <td class="px-2 py-2 text-center">
                                                <input v-model="item.unit_price" type="number" step="0.01" min="0" class="w-24 border-gray-300 rounded-md text-sm p-1 text-center" :readonly="isCustomer" @input="calculateRowTotal(index)" required>
                                            </td>
                                            <td class="px-2 py-2 text-center font-semibold">{{ item.total }}</td>
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
                                    Create Quotation
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style>
.vue-select-custom .vs__dropdown-toggle {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 2px 0;
}
.dark .vue-select-custom .vs__dropdown-toggle {
    border-color: #374151;
    background-color: #111827;
}
.dark .vue-select-custom .vs__selected,
.dark .vue-select-custom .vs__search {
    color: #d1d5db;
}
.dark .vue-select-custom .vs__dropdown-menu {
    background-color: #1f2937;
    color: #d1d5db;
}
.dark .vue-select-custom .vs__dropdown-option--highlight {
    background-color: #4f46e5;
    color: #ffffff;
}
</style>
