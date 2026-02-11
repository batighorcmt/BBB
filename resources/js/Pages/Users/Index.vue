<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('users.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

import Swal from 'sweetalert2';

const updateStatus = (user) => {
    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    Swal.fire({
        title: 'Are you sure?',
        text: `You want to change status to ${newStatus}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('users.status', user.id), { status: newStatus });
        }
    });
};

const resetPassword = (user) => {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password will be set to "123456". This cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, reset it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('users.reset-password', user.id));
        }
    });
};

const getRoleName = (user) => {
    return user.roles && user.roles.length > 0 ? user.roles[0].name : user.role;
};
</script>

<template>
    <Head title="Users List" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Users List</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Search and Filter -->
                        <div class="flex justify-between mb-6">
                            <div class="relative">
                                <input v-model="search" type="text" placeholder="Search users..." class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pl-10 pr-4 py-2">
                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto border rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reset Password</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(user, index) in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ user.username }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ getRoleName(user) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button @click="resetPassword(user)" class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 font-medium">Reset</button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="updateStatus(user)" 
                                                    :class="[
                                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                                        user.status === 'active' ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200'
                                                    ]">
                                                {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex justify-between items-center" v-if="users.links.length > 3">
                            <div>
                                <span class="text-sm text-gray-700 dark:text-gray-400">
                                    Showing <span class="font-medium">{{ users.from }}</span> to <span class="font-medium">{{ users.to }}</span> of <span class="font-medium">{{ users.total }}</span> results
                                </span>
                            </div>
                            <div class="flex space-x-1">
                                <Link v-for="(link, k) in users.links" :key="k" 
                                      :href="link.url" 
                                      v-html="link.label"
                                      :class="['px-3 py-1 border rounded text-sm', link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                                      :preserve-scroll="true"
                                />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
