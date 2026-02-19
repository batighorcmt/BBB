<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    attendances: Array,
    matrix: Array,
    daysInMonth: Number,
    filters: Object,
});

const filterDate = ref(props.filters.date || '');
const filterMonth = ref(props.filters.month || '');
const showModal = ref(false);
const modalImage = ref('');

const isMonthlyView = computed(() => !!filterMonth.value && props.matrix?.length > 0);


const handleFilter = () => {
    router.get(route('attendance.reports'), {
        date: filterDate.value,
        month: filterMonth.value,
    }, { preserveState: true });
};

const printReport = () => {
    window.print();
};

const openImageModal = (image) => {
    if (image) {
        modalImage.value = '/storage/' + image;
        showModal.value = true;
    }
};

const formatTime = (time) => {
    if (!time) return '--:--:--';
    return time;
};

const formatWorkHours = (minutes) => {
    if (!minutes) return '-';
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${h}h ${m}m`;
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
                        <!-- Matrix View -->
                        <table v-if="isMonthlyView" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase sticky left-0 bg-gray-50 dark:bg-gray-700 z-10">Employee</th>
                                    <th v-for="day in daysInMonth" :key="day" class="px-1 py-2 text-center font-medium text-gray-500 dark:text-gray-300">{{ day }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 uppercase">
                                <tr v-for="row in matrix" :key="row.employee.id">
                                    <td class="px-2 py-2 whitespace-nowrap dark:text-gray-300 font-bold sticky left-0 bg-white dark:bg-gray-800 z-10 border-r">
                                        {{ row.employee.name }}
                                    </td>
                                    <td v-for="day in daysInMonth" :key="day" class="px-1 py-2 text-center border-r last:border-0" :class="row.days[day].status === 'P' ? 'text-green-600 font-bold' : 'text-red-400 opacity-50'">
                                        {{ row.days[day].status }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- List View -->
                        <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Staff Name (Code)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap text-center">Check IN</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap text-center">Check OUT</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap text-center">Work Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap text-center">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap text-center">Photo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="att in attendances" :key="att.id">
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 font-medium">
                                        {{ new Date(att.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 font-medium">
                                        {{ att.employee.name }} ({{ att.employee.employee_code }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 text-center font-mono text-green-600 font-bold">
                                        {{ att.check_in }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 text-center font-mono text-red-600 font-bold">
                                        {{ formatTime(att.check_out) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 text-center font-bold">
                                        {{ formatWorkHours(att.work_hours) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 text-center space-x-2 no-print">
                                        <a v-if="att.check_in_latitude" :href="`https://www.google.com/maps?q=${att.check_in_latitude},${att.check_in_longitude}`" target="_blank" class="text-blue-500 text-xs hover:underline">
                                            IN Link
                                        </a>
                                        <span v-else class="text-gray-400">-</span>
                                        <span class="text-gray-300">|</span>
                                        <a v-if="att.check_out_latitude" :href="`https://www.google.com/maps?q=${att.check_out_latitude},${att.check_out_longitude}`" target="_blank" class="text-red-500 text-xs hover:underline">
                                            OUT Link
                                        </a>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300 text-center space-x-3 no-print">
                                        <button v-if="att.check_in_photo" @click="openImageModal(att.check_in_photo)" class="text-blue-500 hover:text-blue-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </button>
                                        <button v-if="att.check_out_photo" @click="openImageModal(att.check_out_photo)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </button>
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
