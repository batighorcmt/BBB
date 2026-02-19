<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Dashboard</h2>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Total Pending Quotations -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">মোট পেন্ডিং কোটেশন</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ stats?.pendingQuotations || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sales Amount -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">মোট সেলস টাকা</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">৳{{ stats?.totalSalesAmount ? Number(stats.totalSalesAmount).toLocaleString() : 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sales Due -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">মোট সেলস বকেয়া</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">৳{{ stats?.totalSalesDue ? Number(stats.totalSalesDue).toLocaleString() : 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Purchase Amount -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">মোট ক্রয় টাকার পরিমান</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">৳{{ stats?.totalPurchaseAmount ? Number(stats.totalPurchaseAmount).toLocaleString() : 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Purchase Due -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-orange-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">মোট ক্রয় বাকীর পরিমান</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">৳{{ stats?.totalPurchaseDue ? Number(stats.totalPurchaseDue).toLocaleString() : 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Active Customers -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">মোট সক্রিয় কাস্টমার সংখ্যা</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ stats?.activeCustomers || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Welcome Message -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">স্বাগতম!</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        আপনার Production Management System এ স্বাগতম। এখানে আপনি আপনার সকল ব্যবসায়িক কার্যক্রম পরিচালনা করতে পারবেন।
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
