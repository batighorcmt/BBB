<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    module: String,
});
</script>

<template>
    <Head :title="module" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-4 text-2xl font-semibold text-gray-800 dark:text-white">{{ module }} Module</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">এই মডিউলটি শীঘ্রই আসছে...</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">This module is coming soon...</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
