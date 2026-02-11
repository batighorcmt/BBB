<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    permissions: Object
});

const form = useForm({
    name: '',
    description: '',
    permissions: []
});

const togglePermission = (permissionName) => {
    const index = form.permissions.indexOf(permissionName);
    if (index === -1) {
        form.permissions.push(permissionName);
    } else {
        form.permissions.splice(index, 1);
    }
};

const toggleGroup = (groupName, groupPermissions) => {
    const allSelected = groupPermissions.every(p => form.permissions.includes(p.name));
    
    if (allSelected) {
        // Deselect all
        groupPermissions.forEach(p => {
            const index = form.permissions.indexOf(p.name);
            if (index !== -1) form.permissions.splice(index, 1);
        });
    } else {
        // Select all
        groupPermissions.forEach(p => {
            if (!form.permissions.includes(p.name)) {
                form.permissions.push(p.name);
            }
        });
    }
};

const isGroupSelected = (groupPermissions) => {
    return groupPermissions.every(p => form.permissions.includes(p.name));
};

const submit = () => {
    form.post(route('roles.store'));
};
</script>

<template>
    <Head title="Create Role" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create Role</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <form @submit.prevent="submit">
                            <!-- Role Info -->
                            <div class="mb-6">
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Role Name *</label>
                                <input v-model="form.name" type="text" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full" required />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-6">
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Description</label>
                                <textarea v-model="form.description" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full"></textarea>
                            </div>

                            <!-- Permissions Matrix -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium mb-4">Permissions</h3>
                                <div class="border rounded-md overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Module</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">Select All</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Specific Permissions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(groupPermissions, groupName) in permissions" :key="groupName">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ groupName }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <label class="inline-flex items-center">
                                                        <input type="checkbox" :checked="isGroupSelected(groupPermissions)" @change="toggleGroup(groupName, groupPermissions)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Select All</span>
                                                    </label>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <label v-for="permission in groupPermissions" :key="permission.id" class="inline-flex items-center">
                                                            <input type="checkbox" :value="permission.name" v-model="form.permissions" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ permission.name }}</span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <Link :href="route('roles.index')" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</Link>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" :disabled="form.processing">
                                    Create Role
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
