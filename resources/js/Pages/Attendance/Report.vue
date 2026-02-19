<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    attendances: Array,
    filters: Object,
});

const filterDate = ref(props.filters.date || '');
const filterMonth = ref(props.filters.month || '');

const handleFilter = () => {
    router.get(route('attendance.reports'), {
        date: filterDate.value,
        month: filterMonth.value,
    }, { preserveState: true });
};

const printReport = () => {
    window.print();
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'present': return 'bg-green-100 text-green-800';
        case 'late': return 'bg-yellow-100 text-yellow-800';
        case 'absent': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Attendance Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center no-print">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Attendance Reports
                </h2>
                <button @click="printReport" class="bg-gray-800 text-white px-4 py-2 rounded shadow hover:bg-gray-900">
                    Print Report
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6 no-print">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Report</label>
                            <input type="date" v-model="filterDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Report</label>
                            <input type="month" v-model="filterMonth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div class="flex space-x-2">
                            <button @click="handleFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Filter</button>
                            <button @click="filterDate = ''; filterMonth = ''; handleFilter()" class="text-gray-600 hover:text-gray-900">Reset</button>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 print-header">
                        <h1 class="text-2xl font-bold text-center dark:text-white">হাজিরা রিপোর্ট</h1>
                        <p class="text-center text-gray-500 mt-1">
                            {{ filterDate ? 'তারিখ: ' + filterDate : (filterMonth ? 'মাস: ' + filterMonth : 'সকল রিপোর্ট') }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Photo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Staff Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Check In</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Check Out</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="att in attendances" :key="att.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img v-if="att.photo_path" :src="'/storage/' + att.photo_path" class="h-10 w-10 rounded-full object-cover border" />
                                        <span v-else class="text-gray-400">N/A</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300">{{ att.employee.employee_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 font-medium">{{ att.employee.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 font-medium">
                                        {{ new Date(att.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 font-mono text-sm text-green-600 font-bold">{{ att.check_in }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 font-mono text-sm text-red-600 font-bold">{{ att.check_out || '--:--:--' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 text-xs">
                                        <a v-if="att.latitude" :href="`https://www.google.com/maps?q=${att.latitude},${att.longitude}`" target="_blank" class="text-blue-600 hover:underline">
                                            Maps View
                                        </a>
                                        <span v-else>No GPS</span>
                                    </td>
                                </tr>
                                <tr v-if="attendances.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">কোনো হাজিরা পাওয়া যায়নি।</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print { display: none !important; }
    .print-header { display: block !important; }
    body { background: white !important; }
    .max-w-7xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    table { width: 100% !important; border-collapse: collapse; }
    th, td { border: 1px solid #ddd !important; }
}
</style>
