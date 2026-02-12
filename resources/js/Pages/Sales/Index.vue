<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { throttle } from 'lodash';
import Swal from 'sweetalert2';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const showPaymentModal = ref(false);
const selectedSale = ref(null);
const activeDropdownId = ref(null);

const toggleDropdown = (event, id) => {
    event.stopPropagation();
    activeDropdownId.value = activeDropdownId.value === id ? null : id;
};

onMounted(() => {
    const closeDropdown = () => {
        activeDropdownId.value = null;
    };
    window.addEventListener('click', closeDropdown);
    return () => window.removeEventListener('click', closeDropdown);
});

const paymentForm = useForm({
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'Cash',
    reference_no: '',
    notes: '',
});

watch(search, throttle((value) => {
    router.get(
        route('sales.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'bg-green-500';
        case 'pending': return 'bg-yellow-500';
        case 'cancelled': return 'bg-red-500';
        case 'delivered': return 'bg-teal-500';
        default: return 'bg-gray-500';
    }
};

const openPaymentModal = (sale) => {
    selectedSale.ref = sale;
    selectedSale.value = sale;
    paymentForm.amount = sale.due_amount;
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    paymentForm.reset();
};

const submitPayment = () => {
    paymentForm.post(route('sales.receive-payment', selectedSale.value.id), {
        onSuccess: () => {
            closePaymentModal();
            Swal.fire('Success', 'Payment received successfully.', 'success');
        },
        onError: () => {
            Swal.fire('Error', 'Failed to process payment.', 'error');
        }
    });
};

const deleteSale = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('sales.destroy', id), {
                onSuccess: () => Swal.fire('Deleted!', 'Sale has been deleted.', 'success'),
                onError: () => Swal.fire('Error!', 'Failed to delete sale.', 'error')
            });
        }
    });
};

const printSale = (id) => {
    window.open(route('sales.show', id) + '?print=1', '_blank');
};
</script>

<template>
    <Head title="Sales List" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sales List</h2>
                <div class="text-sm text-gray-500">Home > Finance > Sales</div>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Top Controls -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0">
                            <div></div>
                            <div class="flex space-x-2">
                                <Link :href="route('sales.create')" class="bg-[#00334e] hover:bg-[#00283d] text-white px-4 py-2 rounded-md text-sm font-medium flex items-center transition shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create Sale
                                </Link>
                            </div>
                        </div>

                        <!-- Filter & Search Bar -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                <span>Show</span>
                                <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white">
                                    <option>10</option>
                                    <option>25</option>
                                    <option>50</option>
                                </select>
                                <span>entries</span>
                            </div>

                            <div class="flex flex-wrap gap-2 justify-center">
                                <button class="px-3 py-1 bg-teal-500 text-white text-xs rounded hover:bg-teal-600 shadow-sm">Copy</button>
                                <button class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 shadow-sm">Excel</button>
                                <button class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 shadow-sm">PDF</button>
                                <button class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 shadow-sm">Print</button>
                            </div>

                            <div class="flex items-center space-x-2 w-full md:w-auto">
                                <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">Search:</span>
                                <input v-model="search" type="text" class="w-full md:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm dark:bg-gray-800 dark:text-white" placeholder="No or Customer...">
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto lg:overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200">Sale No</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200">Customer</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200">Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200">Paid</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200">Due</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 text-[#00334e] dark:text-gray-200 font-bold">Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#00334e] dark:text-gray-200">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(sale, index) in sales.data" :key="sale.id" :class="index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50/50 dark:bg-gray-700/50'" class="hover:bg-blue-50/70 dark:hover:bg-blue-900/20 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 font-bold text-indigo-600 hover:underline">
                                            <Link :href="route('sales.show', sale.id)">{{ sale.sale_no }}</Link>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-medium">{{ sale.customer?.name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 capitalize">{{ sale.type }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 font-bold text-gray-800 dark:text-gray-100">{{ sale.grand_total }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-green-600 font-bold">{{ sale.paid_amount }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-red-600 font-bold leading-none">
                                            <span>{{ sale.due_amount }}</span>
                                            <div v-if="parseFloat(sale.due_amount) > 0" class="mt-1">
                                                <button @click="openPaymentModal(sale)" class="text-[10px] text-blue-500 hover:text-blue-700 underline font-normal">Receive Due</button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm border-r border-gray-200 dark:border-gray-700 text-center">
                                            <span :class="['px-2 py-1 text-[10px] font-bold text-white rounded uppercase tracking-wider shadow-sm', getStatusColor(sale.status)]">
                                                {{ sale.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="relative inline-block text-left">
                                                <button @click.stop="(e) => toggleDropdown(e, sale.id)" class="inline-flex justify-center w-full px-3 py-1.5 text-xs font-bold text-white bg-[#00334e] rounded-md hover:bg-[#00283d] transition-all shadow-sm active:scale-95">
                                                    Action
                                                    <svg class="-mr-1 ml-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </button>
                                                <!-- Dynamic Positioning: Open upwards if in the bottom rows -->
                                                <div v-show="activeDropdownId === sale.id"
                                                     :class="[
                                                        'absolute right-0 w-48 z-50 transform transition-all duration-200',
                                                        index >= sales.data.length - 2 ? 'bottom-full mb-2 origin-bottom-right' : 'top-full origin-top-right mt-0 pt-2'
                                                     ]">
                                                    <div class="rounded-lg shadow-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 py-1 overflow-hidden ring-1 ring-black ring-opacity-10">
                                                        <Link :href="route('sales.show', sale.id)" class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                            <svg class="w-4 h-4 text-[#00334e] dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            <span>View Details</span>
                                                        </Link>
                                                        <button @click="printSale(sale.id)" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                            <span>Print Invoice</span>
                                                        </button>
                                                        <button @click="printSale(sale.id)" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b dark:border-gray-700">
                                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h1.5m1.5 0H13m-4 4h4m-4 4h4"/></svg>
                                                            <span>Download PDF</span>
                                                        </button>
                                                        <button v-if="parseFloat(sale.due_amount) > 0" @click="openPaymentModal(sale)" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors font-bold">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span>Receive Due Payment</span>
                                                        </button>
                                                        <Link :href="route('sales.edit', sale.id)" class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-yellow-50 dark:hover:bg-yellow-900/10 transition-colors">
                                                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            <span>Edit Sale</span>
                                                        </Link>
                                                        <button @click="deleteSale(sale.id)" class="w-full flex items-center space-x-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            <span>Delete</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="sales.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No sales records available.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex flex-col md:flex-row justify-between items-center bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="text-sm text-gray-700 dark:text-gray-400 mb-4 md:mb-0">
                                Showing <span class="font-bold text-[#00334e] dark:text-gray-200">{{ (sales.current_page - 1) * sales.per_page + 1 }}</span> to <span class="font-bold text-[#00334e] dark:text-gray-200">{{ Math.min(sales.current_page * sales.per_page, sales.total) }}</span> of <span class="font-bold text-[#00334e] dark:text-gray-200">{{ sales.total }}</span> entries
                            </div>
                            <div v-if="sales.links.length > 3" class="flex justify-center">
                                <template v-for="(link, key) in sales.links" :key="key">
                                    <div v-if="link.url === null" class="mr-1 px-3 py-1.5 text-xs text-gray-400 border rounded-md bg-white dark:bg-gray-800 dark:border-gray-600 shadow-sm" v-html="link.label" />
                                    <Link v-else :class="{ 'bg-[#00334e] text-white border-[#00334e] font-bold shadow-md': link.active, 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active }" class="mr-1 px-3 py-1.5 text-xs border rounded-md shadow-sm transition-all active:scale-95" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="closePaymentModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    <form @submit.prevent="submitPayment">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-bold text-[#00334e] dark:text-white" id="modal-title">Receive Due Payment</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sale No: <span class="font-bold">{{ selectedSale?.sale_no }}</span> | Remaining Due: <span class="font-bold text-red-500">{{ selectedSale?.due_amount }}</span></p>
                                </div>
                            </div>
                            
                            <div class="space-y-4 mt-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Payment Date</label>
                                        <input v-model="paymentForm.payment_date" type="date" class="w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm text-sm focus:ring-[#00334e]">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Amount to Receive</label>
                                        <input v-model="paymentForm.amount" type="number" step="0.01" :max="selectedSale?.due_amount" class="w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm text-sm font-bold text-green-600 focus:ring-[#00334e]">
                                        <div v-if="paymentForm.errors.amount" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.amount }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                                        <select v-model="paymentForm.payment_method" class="w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm text-sm focus:ring-[#00334e]">
                                            <option>Cash</option>
                                            <option>Bank Transfer</option>
                                            <option>bKash</option>
                                            <option>Nagad</option>
                                            <option>Cheque</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Ref No / Note</label>
                                        <input v-model="paymentForm.reference_no" type="text" placeholder="Optional" class="w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm text-sm focus:ring-[#00334e]">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Internal Note</label>
                                    <textarea v-model="paymentForm.notes" rows="2" class="w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm text-sm focus:ring-[#00334e]"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:gap-2">
                            <button type="submit" :disabled="paymentForm.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-all active:scale-95">
                                {{ paymentForm.processing ? 'Processing...' : 'Confirm Receipt' }}
                            </button>
                            <button @click="closePaymentModal" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:w-auto sm:text-sm transition-all active:scale-95">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
