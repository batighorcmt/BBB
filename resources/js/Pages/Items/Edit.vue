<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    item: Object,
});

const form = useForm({
    _method: 'PUT', // Needed for file upload in Inertia when updating
    name: props.item.name,
    code: props.item.code, // Included for display, though disabled in UI
    type: props.item.type,
    price: props.item.price,
    cost: props.item.cost,
    stock_quantity: props.item.stock_quantity,
    unit: props.item.unit,
    description: props.item.description,
    status: props.item.status,
    // Specifications
    bag_type: props.item.bag_type || '',
    usage: props.item.usage || '',
    size: props.item.size || '',
    capacity: props.item.capacity || '',
    gsm: props.item.gsm || '',
    fabric_color: props.item.fabric_color || '',
    fabric_quality: props.item.fabric_quality || '',
    lamination: props.item.lamination || '',
    print_type: props.item.print_type || '',
    print_color_count: props.item.print_color_count || '',
    print_sides: props.item.print_sides || '',
    brand_name: props.item.brand_name || '',
    design_file: null, // File input
    handle_type: props.item.handle_type || '',
    handle_color: props.item.handle_color || '',
    stitching_type: props.item.stitching_type || '',
    bottom_finish: props.item.bottom_finish || '',
});

const submit = () => {
    form.post(route('items.update', props.item.id));
};
</script>

<template>
    <Head title="Edit Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Item</h2>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            
                            <!-- Basic Info Section -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">Basic Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    <!-- Item Type Selection -->
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Type <span class="text-red-500">*</span></label>
                                        <div class="flex space-x-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.type" value="product" class="form-radio text-indigo-600">
                                                <span class="ml-2">Product (Stocked)</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" v-model="form.type" value="service" class="form-radio text-purple-600">
                                                <span class="ml-2">Service (Non-stocked)</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                                        <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Code</label>
                                        <input v-model="form.code" type="text" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-600 text-gray-500 cursor-not-allowed" disabled>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling Price <span class="text-red-500">*</span></label>
                                        <input v-model="form.price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                        <div v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price</label>
                                        <input v-model="form.cost" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <div v-if="form.errors.cost" class="text-red-500 text-xs mt-1">{{ form.errors.cost }}</div>
                                    </div>

                                    <!-- Stock Fields (Only for Product) -->
                                    <div v-if="form.type === 'product'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Quantity</label>
                                        <input v-model="form.stock_quantity" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <div v-if="form.errors.stock_quantity" class="text-red-500 text-xs mt-1">{{ form.errors.stock_quantity }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit (e.g., pcs, kg)</label>
                                        <input v-model="form.unit" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <div v-if="form.errors.unit" class="text-red-500 text-xs mt-1">{{ form.errors.unit }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                                    </div>

                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                        <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Specifications Section (Only for Product) -->
                            <div v-if="form.type === 'product'">
                                <!-- Bag Specifications Section -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">Bag Specifications</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bag Type</label>
                                            <select v-model="form.bag_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Type</option>
                                                <option value="D-cut">D-cut</option>
                                                <option value="W-cut">W-cut</option>
                                                <option value="Loop Handle">Loop Handle</option>
                                                <option value="Box Bag">Box Bag</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage</label>
                                            <input v-model="form.usage" type="text" placeholder="e.g. Shopping" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Size (H x W x G)</label>
                                            <input v-model="form.size" type="text" placeholder="e.g. 10x12x4 inch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity (Kg)</label>
                                            <input v-model="form.capacity" type="text" placeholder="e.g. 5 kg" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                    </div>
                                </div>

                                <!-- Material Info -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">Material Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">GSM</label>
                                            <input v-model="form.gsm" type="text" placeholder="e.g. 80" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fabric Color</label>
                                            <input v-model="form.fabric_color" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quality</label>
                                            <select v-model="form.fabric_quality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Quality</option>
                                                <option value="Virgin">Virgin</option>
                                                <option value="Recycled">Recycled</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lamination</label>
                                            <select v-model="form.lamination" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Option</option>
                                                <option value="No Lamination">No Lamination</option>
                                                <option value="Matte">Matte</option>
                                                <option value="Glossy">Glossy</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Printing Info -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">Printing Details</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Print Type</label>
                                            <select v-model="form.print_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Type</option>
                                                <option value="Screen">Screen</option>
                                                <option value="Offset">Offset</option>
                                                <option value="Flexo">Flexo</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color Count</label>
                                            <select v-model="form.print_color_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Count</option>
                                                <option value="1 Color">1 Color</option>
                                                <option value="2 Colors">2 Colors</option>
                                                <option value="Full Color">Full Color</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Print Sides</label>
                                            <select v-model="form.print_sides" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Sides</option>
                                                <option value="Single Side">Single Side</option>
                                                <option value="Double Side">Double Side</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand/Logo Name</label>
                                            <input v-model="form.brand_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div class="col-span-1 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Design File</label>
                                            <div v-if="item.design_file" class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                                                Current file: <a :href="'/storage/' + item.design_file" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">View File</a>
                                            </div>
                                            <input type="file" @input="form.design_file = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                                            <div v-if="form.errors.design_file" class="text-red-500 text-xs mt-1">{{ form.errors.design_file }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Finishing & Handle -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">Finishing & Handle</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                         <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Handle Type</label>
                                            <select v-model="form.handle_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Type</option>
                                                <option value="Loop">Loop</option>
                                                <option value="D-cut">D-cut</option>
                                                <option value="Rope">Rope</option>
                                                <option value="None">None</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Handle Color</label>
                                            <input v-model="form.handle_color" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                         <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stitching Type</label>
                                            <select v-model="form.stitching_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Type</option>
                                                <option value="Stitching">Stitching</option>
                                                <option value="Heat Sealing">Heat Sealing</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bottom Finish</label>
                                             <select v-model="form.bottom_finish" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="">Select Finish</option>
                                                <option value="Box Bottom">Box Bottom</option>
                                                <option value="Stitch Bottom">Stitch Bottom</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="mt-6 flex justify-end">
                                <Link :href="route('items.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</Link>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded" :disabled="form.processing">
                                    Update Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
