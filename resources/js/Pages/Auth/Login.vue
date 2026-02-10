<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#00334e]">
            <!-- Header / Logo Area -->
            <div class="mb-8 text-center animate-fade-in-down">
                <div class="inline-flex justify-center items-center w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Production Management</h1>
                <p class="text-gray-300 mt-2 text-sm">Please sign in to continue</p>
            </div>

            <!-- Login Card -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-xl animate-fade-in-up">
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 border border-green-200 bg-green-50 p-2 rounded">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="email"
                            type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-[#00334e] focus:ring-[#00334e] rounded-md shadow-sm transition-colors duration-200"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Enter your email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <InputLabel for="password" value="Password" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full border-gray-300 focus:border-[#00334e] focus:ring-[#00334e] rounded-md shadow-sm transition-colors duration-200"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mt-4">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" class="text-[#00334e] focus:ring-[#00334e]" />
                            <span class="ms-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="underline text-sm text-gray-600 hover:text-[#00334e] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00334e] transition-colors"
                        >
                            Forgot your password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-[#00334e] hover:bg-[#00283d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00334e] transition-all duration-200 uppercase tracking-widest"
                            :class="{ 'opacity-75 cursor-wait': form.processing }"
                        >
                             <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Signing in...' : 'Log In' }}
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
             <div class="mt-8 text-center text-gray-400 text-xs">
                 &copy; 2026 Production Management System.
             </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out both; 
    animation-delay: 0.1s;
}
</style>