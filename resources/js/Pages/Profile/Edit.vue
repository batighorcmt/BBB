<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import PasswordInput from '@/Components/PasswordInput.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: 'PATCH',
    name: user.name,
    email: user.email,
    photo: null,
    current_password: '',
    password: '',
    password_confirmation: '',
});

const photoPreview = ref(user.profile_photo_path ? `/storage/${user.profile_photo_path}` : null);
const photoInput = ref(null);

const updatePhotoPreview = () => {
    const file = photoInput.value.files[0];
    if (!file) return;

    form.photo = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
             form.reset('current_password', 'password', 'password_confirmation');
             // Optionally clear photo input if successful
        },
    });
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Main Profile Form -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section> <!-- class="max-w-xl" removed strict max-width to allow better responsiveness, but could keep it -->
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Information</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Update your account's profile information, email address, and profile photo.
                            </p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            
                            <!-- Profile Photo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Photo</label>
                                <div class="flex items-center space-x-6">
                                    <div class="shrink-0 relative group cursor-pointer" @click="selectNewPhoto">
                                        <img v-if="photoPreview" :src="photoPreview" class="h-24 w-24 object-cover rounded-full border-2 border-gray-200 shadow-sm" alt="Profile Photo" />
                                        <div v-else class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-200 text-indigo-500 font-bold text-2xl shadow-sm">
                                            {{ form.name.charAt(0).toUpperCase() }}
                                        </div>
                                        
                                        <!-- Overlay for hover effect -->
                                        <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                    </div>
                                    
                                    <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" accept="image/*">
                                    
                                    <div>
                                        <button type="button" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium focus:outline-none" @click="selectNewPhoto">
                                            Change Photo
                                        </button>
                                        <p class="text-xs text-gray-500 mt-1">JPG, GIF or PNG. Max 1MB.</p>
                                        <div v-if="form.errors.photo" class="text-red-500 text-xs mt-1">{{ form.errors.photo }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required autofocus autocomplete="name" />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required autocomplete="username" />
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>
                            
                            <!-- Password Change Section -->
                            <div class="border-t pt-6 mt-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Update Password</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                                        <PasswordInput v-model="form.current_password" class="mt-1 block w-full" autocomplete="current-password" />
                                        <div v-if="form.errors.current_password" class="text-red-500 text-xs mt-1">{{ form.errors.current_password }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                                        <PasswordInput v-model="form.password" class="mt-1 block w-full" autocomplete="new-password" />
                                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                        <PasswordInput v-model="form.password_confirmation" class="mt-1 block w-full" autocomplete="new-password" />
                                        <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition ease-in-out duration-150" :disabled="form.processing">
                                    Save Changes
                                </button>

                                <transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                                </transition>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Delete Account Section (Optional, could be added later if needed) -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
