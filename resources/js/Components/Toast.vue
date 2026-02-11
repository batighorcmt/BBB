<script setup>
import { onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    flash: Object, // Receive flash object directly
});

const isVisible = ref(false);
const toastMessage = ref('');
const toastType = ref('');
const toastTimeout = ref(null);

// Watch for flash messages from props
watch(() => props.flash, (flash) => {
    if (!flash) return;
    
    // Clear existing timeout if any
    if (toastTimeout.value) {
        clearTimeout(toastTimeout.value);
    }

    if (flash.success) {
        showToast(flash.success, 'success');
    } else if (flash.error) {
        showToast(flash.error, 'error');
    } else if (flash.warning) {
        showToast(flash.warning, 'warning');
    } else if (flash.info) {
        showToast(flash.info, 'info');
    }
}, { deep: true, immediate: true });

const showToast = (message, type) => {
    if (!message) return;
    toastMessage.value = message;
    toastType.value = type;
    isVisible.value = true;

    toastTimeout.value = setTimeout(() => {
        isVisible.value = false;
    }, 4000); // Hide after 4 seconds
};

const closeToast = () => {
    isVisible.value = false;
};

const typeClasses = {
    success: 'bg-green-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-white',
    info: 'bg-blue-500 text-white',
};

const iconPaths = {
    success: 'M5 13l4 4L19 7',
    error: 'M6 18L18 6M6 6l12 12',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};
</script>

<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="-translate-y-2 opacity-0 sm:-translate-y-2 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isVisible" class="fixed top-20 right-5 z-50 max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden" :class="typeClasses[toastType]">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPaths[toastType]" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium">
                            {{ toastMessage }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="closeToast" class="bg-transparent rounded-md inline-flex text-white hover:text-gray-200 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
