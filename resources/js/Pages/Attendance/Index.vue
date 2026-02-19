<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    todayAttendance: Object,
    employee: Object,
});

const video = ref(null);
const canvas = ref(null);
const stream = ref(null);
const screenshot = ref(null);
const location = ref({ lat: null, lng: null });
const address = ref('');
const error = ref('');
const loading = ref(false);

const form = useForm({
    image: '',
    latitude: null,
    longitude: null,
    location_address: '',
});

const startCamera = async () => {
    try {
        stream.value = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user' } 
        });
        if (video.value) {
            video.value.srcObject = stream.value;
        }
    } catch (err) {
        error.value = "Camera access denied or not available.";
    }
};

const getLocation = () => {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                location.value = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                form.latitude = location.value.lat;
                form.longitude = location.value.lng;
                
                // Reverse Geocoding (Optional, using a public API or just showing coordinates)
                address.value = `Lat: ${location.value.lat}, Lng: ${location.value.lng}`;
                form.location_address = address.value;
            },
            (err) => {
                error.value = "Location access denied. Please enable location to mark attendance.";
            }
        );
    } else {
        error.value = "Geolocation is not supported by your browser.";
    }
};

const capture = () => {
    const context = canvas.value.getContext('2d');
    canvas.value.width = video.value.videoWidth;
    canvas.value.height = video.value.videoHeight;
    context.drawImage(video.value, 0, 0, canvas.value.width, canvas.value.height);
    screenshot.value = canvas.value.toDataURL('image/jpeg');
    form.image = screenshot.value;
};

const submit = () => {
    if (!form.image) return alert('Please capture a photo first.');
    if (!form.latitude) return alert('Please wait for location to be acquired.');
    
    form.post(route('attendance.store'), {
        preserveScroll: true,
        onSuccess: () => {
            screenshot.value = null;
            form.image = '';
        }
    });
};

onMounted(() => {
    getLocation();
    startCamera();
});

onUnmounted(() => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
    }
});
</script>

<template>
    <Head title="Attendance" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Daily Attendance
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <div v-if="!employee" class="text-red-500 mb-4">
                        আপনি কোনো এমপ্লয়ী রোলে যুক্ত নেই। দয়া করে এডমিনের সাথে যোগাযোগ করুন।
                    </div>

                    <div v-else-if="todayAttendance && todayAttendance.check_out" class="text-center py-10">
                        <div class="text-green-500 mb-4">
                            <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold dark:text-white">আজকের হাজিরা সম্পন্ন হয়েছে!</h3>
                        <p class="text-gray-500 mt-2">Check-in: {{ todayAttendance.check_in }} | Check-out: {{ todayAttendance.check_out }}</p>
                    </div>

                    <div v-else>
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                {{ todayAttendance ? 'আজকের তথ্য: Check-out করুন' : 'আজকের তথ্য: Check-in করুন' }}
                            </h3>
                            <p class="text-sm text-gray-500">{{ new Date().toLocaleDateString('bn-BD') }} | {{ new Date().toLocaleTimeString() }}</p>
                        </div>

                        <!-- Camera Section -->
                        <div class="relative bg-black rounded-lg overflow-hidden aspect-video mb-6">
                            <video v-show="!screenshot" ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                            <img v-if="screenshot" :src="screenshot" class="w-full h-full object-cover" />
                            
                            <div v-if="error" class="absolute inset-0 flex items-center justify-center bg-red-900/50 text-white p-4 text-center">
                                {{ error }}
                            </div>
                        </div>

                        <canvas ref="canvas" class="hidden"></canvas>

                        <div class="flex justify-between items-center mb-6">
                            <div class="text-sm dark:text-gray-300">
                                <div class="flex items-center space-x-2">
                                    <span :class="form.latitude ? 'text-green-500' : 'text-gray-400'">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                    </span>
                                    <span>{{ form.latitude ? 'লোকেশন পাওয়া গেছে' : 'লোকেশন খোঁজা হচ্ছে...' }}</span>
                                </div>
                            </div>


                            <button v-if="!screenshot" @click="capture" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 011.664.89l.812 1.22A2 2 0 0010.07 10H13.93a2 2 0 001.664-1.89l.812-1.22A2 2 0 0118.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                ছবি তুলুন
                            </button>
                            <button v-else @click="screenshot = null; form.image = ''" class="text-red-500 font-medium">
                                আবার তুলুন
                            </button>
                        </div>

                        <button 
                            @click="submit" 
                            :disabled="form.processing || !form.image || !form.latitude"
                            class="w-full py-4 rounded-xl text-white font-bold text-lg transition shadow-lg"
                            :class="[
                                todayAttendance ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700',
                                (form.processing || !form.image || !form.latitude) ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ form.processing ? 'প্রসেসিং হচ্ছে...' : (todayAttendance ? 'Check-out করুন' : 'Check-in করুন') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
