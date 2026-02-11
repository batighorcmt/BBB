<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    productions: Object,
});

const search = ref('');

const deleteProduction = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('production.destroy', id), {
                onSuccess: () => Swal.fire('Deleted!', 'Production has been deleted.', 'success')
            });
        }
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'working': return 'bg-blue-100 text-blue-800';
        case 'completed': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Production List" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Production Management</h2>
                <Link :href="route('production.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    + New Production
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8"> <!-- Full Width Logic: w-full -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prod No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quotation</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="prod in productions.data" :key="prod.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ prod.production_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ prod.quotation?.quotation_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ prod.quotation?.customer?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ prod.total_cost }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColor(prod.status)]">
                                                {{ prod.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ prod.creator?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('production.edit', prod.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                            <button @click="deleteProduction(prod.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="productions.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No production records found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                         <!-- Pagination -->
                        <div class="mt-4" v-if="productions.links.length > 3">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, k) in productions.links" :key="k">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-blue-700 text-white': link.active }" />
                                    <span v-else v-html="link.label" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"></span>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
