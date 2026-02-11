<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    roles: Object,
    filters: Object,
});

const form = useForm({});

import Swal from 'sweetalert2';

const deleteRole = (id) => {
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
            form.delete(route('roles.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Role has been deleted.',
                        'success'
                    )
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Roles List</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Manage Roles</h3>
                            <Link :href="route('roles.create')" class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700">
                                + Add Role
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(role, index) in roles.data" :key="role.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ role.name }}</td>
                                        <td class="px-6 py-4 whitespace-normal text-sm">{{ role.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('roles.edit', role.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-3">Edit</Link>
                                            <button @click="deleteRole(role.id)" class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="roles.data.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No roles found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4" v-if="roles.links">
                            <!-- Implement pagination component here if needed, or simple links -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
