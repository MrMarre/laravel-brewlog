<script setup lang="ts">
import flavorNotes from '@/data/flavor_notes.json';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Brewlog', href: '/brewlog' }];

interface Flavor {
    id: number;
    flavor: string;
}

interface SelectedFlavor {
    flavor_id: number;
    type: 'listed' | 'tasted';
}

interface NewLog {
    brand_name: string;
    product_name: string;
    brew_method: string;
    grind_size: string;
    coffee_weight: number;
    water_weight: number;
    bloom_time: number;
    brew_time: number;
    flavors: SelectedFlavor[];
}

const newLog = ref<NewLog>({
    brand_name: '',
    product_name: '',
    brew_method: '',
    grind_size: '',
    coffee_weight: 0,
    water_weight: 0,
    bloom_time: 0,
    brew_time: 0,
    flavors: [],
});
// Submit log
async function submitLog() {
    try {
        console.log('Submitting brew log:', newLog.value);
        alert('Brew log added! (fake, since JSON only)');
        newLog.value = {
            brand_name: '',
            product_name: '',
            brew_method: '',
            grind_size: '',
            coffee_weight: 0,
            water_weight: 0,
            bloom_time: 0,
            brew_time: 0,
            flavors: [],
        };
    } catch (err) {
        console.error(err);
        alert('Failed to add log.');
    }
}

const flavorSearch = ref('');
const dropdownOpen = ref(false);

const flavors = ref<Flavor[]>(flavorNotes.map((flavor, index) => ({ id: index, flavor })));

const filteredFlavors = computed(() => {
    if (!flavorSearch.value.trim()) return flavors.value;
    return flavors.value.filter((f) => f.flavor.toLowerCase().includes(flavorSearch.value.toLowerCase()));
});

function addFlavor(flavorId: number, type: 'listed' | 'tasted') {
    if (!newLog.value.flavors.find((f) => f.flavor_id === flavorId && f.type === type)) {
        newLog.value.flavors.push({ flavor_id: flavorId, type });
    }
}
function removeFlavor(flavorId: number, type: 'listed' | 'tasted') {
    newLog.value.flavors = newLog.value.flavors.filter((f) => !(f.flavor_id === flavorId && f.type === type));
}
</script>

<template>
    <Head title="Brewlog" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white p-6 md:min-h-min dark:border-sidebar-border dark:bg-gray-800"
            >
                <h2 class="mb-6 text-2xl font-semibold text-gray-800 dark:text-gray-200">Add New Brew Log</h2>

                <form @submit.prevent="submitLog" class="space-y-6">
                    <!-- Coffee Info -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <input v-model="newLog.brand_name" placeholder="Brand Name" class="input-field" required />
                        <input v-model="newLog.product_name" placeholder="Product Name" class="input-field" required />
                        <input v-model="newLog.brew_method" placeholder="Brew Method" class="input-field" required />
                        <input v-model="newLog.grind_size" placeholder="Grind Size" class="input-field" />
                        <input v-model.number="newLog.coffee_weight" type="number" step="0.1" placeholder="Coffee (g)" class="input-field" required />
                        <input v-model.number="newLog.water_weight" type="number" step="0.1" placeholder="Water (g)" class="input-field" required />
                        <input v-model.number="newLog.bloom_time" type="number" placeholder="Bloom Time (s) if applicable" class="input-field" />
                        <input v-model.number="newLog.brew_time" type="number" placeholder="Brew Time (s)" class="input-field" required />
                    </div>

                    <!-- Flavors -->
                    <div>
                        <h3 class="mb-2 font-semibold text-gray-700 dark:text-gray-300">Select Flavors</h3>

                        <!-- Search Input -->
                        <div class="relative mb-3" @focusin="dropdownOpen = true" @focusout="dropdownOpen = false">
                            <input v-model="flavorSearch" placeholder="Search flavors..." class="input-field w-full" />

                            <!-- Dropdown -->
                            <ul
                                v-show="dropdownOpen && filteredFlavors.length"
                                class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-700"
                            >
                                <li
                                    v-for="flavor in filteredFlavors"
                                    :key="flavor.id"
                                    class="flex cursor-pointer items-center justify-between px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600"
                                >
                                    <span class="text-gray-700 dark:text-gray-200">{{ flavor.flavor }}</span>
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @mousedown.prevent
                                            @click="
                                                addFlavor(flavor.id, 'listed');
                                                flavorSearch = '';
                                            "
                                            class="rounded bg-amber-100 px-2 py-1 text-xs text-amber-800 hover:bg-amber-200"
                                        >
                                            Listed
                                        </button>
                                        <button
                                            type="button"
                                            @mousedown.prevent
                                            @click="
                                                addFlavor(flavor.id, 'tasted');
                                                flavorSearch = '';
                                            "
                                            class="rounded bg-green-100 px-2 py-1 text-xs text-green-800 hover:bg-green-200"
                                        >
                                            Tasted
                                        </button>
                                    </div>
                                </li>
                            </ul>

                            <!-- Empty State -->
                            <div
                                v-show="dropdownOpen && !filteredFlavors.length"
                                class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-gray-500 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            >
                                No flavors found
                            </div>
                        </div>

                        <!-- Selected Flavor Tags -->
                        <div class="flex flex-wrap gap-2">
                            <template v-for="(flavor, index) in newLog.flavors" :key="index">
                                <span class="rounded-full px-3 py-1 text-white" :class="flavor.type === 'listed' ? 'bg-amber-500' : 'bg-green-500'">
                                    {{ flavors.find((f) => f.id === flavor.flavor_id)?.flavor }} ({{ flavor.type }})
                                    <button
                                        type="button"
                                        @click="removeFlavor(flavor.flavor_id, flavor.type)"
                                        class="ml-2 text-xs text-white hover:text-gray-200"
                                    >
                                        x
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white transition-colors hover:bg-amber-600 md:w-1/3"
                    >
                        Add Brew Log
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
