<script setup lang="ts">
import flavorNotes from '@/data/flavor_notes.json';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Brewlog', href: '/brewlog' }];
type FlavorType = 'listed' | 'tasted';
type TimeField = 'bloom_time' | 'brew_time';
type TimeInputMode = 'seconds' | 'clock';

interface Flavor {
    id: number;
    flavor: string;
}

interface SelectedFlavor {
    flavor_id: number;
    type: FlavorType;
}

interface NewLog {
    brand_name: string;
    product_name: string;
    brew_method: string;
    grind_size: string;
    coffee_weight: number | '';
    water_weight: number | '';
    bloom_time: number | '';
    brew_time: number | '';
    flavors: SelectedFlavor[];
}

const createEmptyLog = (): NewLog => ({
    brand_name: '',
    product_name: '',
    brew_method: '',
    grind_size: '',
    coffee_weight: '',
    water_weight: '',
    bloom_time: '',
    brew_time: '',
    flavors: [],
});

const newLog = ref<NewLog>(createEmptyLog());
const feedback = ref<{ type: 'success' | 'error'; message: string } | null>(null);
const selectedFlavorType = ref<FlavorType>('listed');
const timeInputMode = ref<TimeInputMode>('seconds');

const flavorSearch = ref('');
const dropdownOpen = ref(false);

const flavors = ref<Flavor[]>(flavorNotes.map((flavor, index) => ({ id: index, flavor })));

const filteredFlavors = computed(() => {
    if (!flavorSearch.value.trim()) return flavors.value;
    return flavors.value.filter((f) => f.flavor.toLowerCase().includes(flavorSearch.value.toLowerCase()));
});

const listedFlavors = computed(() => newLog.value.flavors.filter((flavor) => flavor.type === 'listed'));
const tastedFlavors = computed(() => newLog.value.flavors.filter((flavor) => flavor.type === 'tasted'));
const brewRatio = computed(() => {
    if (typeof newLog.value.coffee_weight !== 'number' || typeof newLog.value.water_weight !== 'number' || newLog.value.coffee_weight <= 0) {
        return null;
    }

    return (newLog.value.water_weight / newLog.value.coffee_weight).toFixed(1);
});

function submitLog() {
    const { coffee_weight, water_weight, brew_time } = newLog.value;

    if (typeof coffee_weight !== 'number' || coffee_weight <= 0) {
        feedback.value = {
            type: 'error',
            message: 'Coffee weight must be greater than 0 grams.',
        };
        return;
    }

    if (typeof water_weight !== 'number' || water_weight <= 0) {
        feedback.value = {
            type: 'error',
            message: 'Water weight must be greater than 0 grams.',
        };
        return;
    }

    if (typeof brew_time !== 'number' || brew_time <= 0) {
        feedback.value = {
            type: 'error',
            message: 'Total brew time must be greater than 0 seconds.',
        };
        return;
    }

    console.log('Submitting brew log:', newLog.value);

    feedback.value = {
        type: 'success',
        message: 'Brew log captured in the UI. Persistence is not wired yet.',
    };

    newLog.value = createEmptyLog();
    flavorSearch.value = '';
    dropdownOpen.value = false;
    selectedFlavorType.value = 'listed';
}

function addFlavor(flavorId: number) {
    if (!newLog.value.flavors.find((flavor) => flavor.flavor_id === flavorId && flavor.type === selectedFlavorType.value)) {
        newLog.value.flavors.push({ flavor_id: flavorId, type: selectedFlavorType.value });
        feedback.value = null;
    }

    flavorSearch.value = '';
}

function removeFlavor(flavorId: number, type: FlavorType) {
    newLog.value.flavors = newLog.value.flavors.filter((f) => !(f.flavor_id === flavorId && f.type === type));
}

function flavorName(flavorId: number) {
    return flavors.value.find((flavor) => flavor.id === flavorId)?.flavor ?? 'Unknown flavor';
}

function getTimeValue(field: TimeField) {
    return newLog.value[field];
}

function getTimeMinutes(field: TimeField) {
    const value = getTimeValue(field);

    if (typeof value !== 'number' || value < 0) {
        return '';
    }

    return Math.floor(value / 60);
}

function getTimeSeconds(field: TimeField) {
    const value = getTimeValue(field);

    if (typeof value !== 'number' || value < 0) {
        return '';
    }

    return value % 60;
}

function readClockInputValue(event: Event) {
    const value = (event.target as HTMLInputElement).value;

    if (value === '') {
        return '';
    }

    return Number.parseInt(value, 10);
}

function updateTimeFromClock(field: TimeField, part: 'minutes' | 'seconds', rawValue: number | '') {
    const currentMinutes = getTimeMinutes(field);
    const currentSeconds = getTimeSeconds(field);
    const minutes = part === 'minutes' ? rawValue : currentMinutes;
    const seconds = part === 'seconds' ? rawValue : currentSeconds;

    if (minutes === '' && seconds === '') {
        newLog.value[field] = '';
        return;
    }

    const normalizedMinutes = typeof minutes === 'number' ? Math.max(0, Math.floor(minutes)) : 0;
    const normalizedSeconds = typeof seconds === 'number' ? Math.max(0, Math.min(59, Math.floor(seconds))) : 0;

    newLog.value[field] = normalizedMinutes * 60 + normalizedSeconds;
}
</script>

<template>
    <Head title="Brewlog" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="relative flex-1 rounded-2xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <div class="mb-8 space-y-2">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Add New Brew Log</h2>
                    <p class="max-w-3xl text-sm text-gray-600 dark:text-gray-300">
                        Capture the brew recipe first, then add flavor notes from the bag and from the cup.
                    </p>
                </div>

                <div
                    v-if="feedback"
                    class="mb-6 rounded-xl border px-4 py-3 text-sm"
                    :class="
                        feedback.type === 'success'
                            ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/60 dark:bg-green-950/30 dark:text-green-200'
                            : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200'
                    "
                >
                    {{ feedback.message }}
                </div>

                <form @submit.prevent="submitLog" class="space-y-8">
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Coffee details</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Identify the coffee and the brew method you used.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2 space-x-2">
                                <label for="brand_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Roaster or brand</label>
                                <input
                                    id="brand_name"
                                    v-model="newLog.brand_name"
                                    placeholder="e.g. Rocket Bean Roasters"
                                    class="input-field"
                                    required
                                />
                            </div>

                            <div class="space-y-2 space-x-2">
                                <label for="product_name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Coffee name</label>
                                <input
                                    id="product_name"
                                    v-model="newLog.product_name"
                                    placeholder="e.g. Ethiopia Guji"
                                    class="input-field"
                                    required
                                />
                            </div>

                            <div class="space-y-2 space-x-2">
                                <label for="brew_method" class="text-sm font-medium text-gray-700 dark:text-gray-300">Brew method</label>
                                <input
                                    id="brew_method"
                                    v-model="newLog.brew_method"
                                    placeholder="e.g. V60, AeroPress, French press"
                                    class="input-field"
                                    required
                                />
                            </div>

                            <div class="space-y-2 space-x-2">
                                <label for="grind_size" class="text-sm font-medium text-gray-700 dark:text-gray-300">Grind size</label>
                                <input id="grind_size" v-model="newLog.grind_size" placeholder="e.g. 18 clicks on Comandante" class="input-field" />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Recipe</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Add the numbers that matter for repeating or adjusting the brew later.
                            </p>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/40"
                        >
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Time input format</span>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
                                    :class="
                                        timeInputMode === 'seconds'
                                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                            : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'
                                    "
                                    @click="timeInputMode = 'seconds'"
                                >
                                    Seconds
                                </button>
                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
                                    :class="
                                        timeInputMode === 'clock'
                                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                            : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'
                                    "
                                    @click="timeInputMode = 'clock'"
                                >
                                    mm:ss
                                </button>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ timeInputMode === 'seconds' ? 'Enter total seconds directly.' : 'Enter minutes and seconds, such as 2m 30s.' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2 space-x-2">
                                <label for="coffee_weight" class="text-sm font-medium text-gray-700 dark:text-gray-300">Coffee weight (g)</label>
                                <input
                                    id="coffee_weight"
                                    v-model.number="newLog.coffee_weight"
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    placeholder="e.g. 15.0"
                                    class="input-field"
                                    required
                                />
                            </div>

                            <div class="space-y-2 space-x-2">
                                <label for="water_weight" class="text-sm font-medium text-gray-700 dark:text-gray-300">Water weight (g)</label>
                                <input
                                    id="water_weight"
                                    v-model.number="newLog.water_weight"
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    placeholder="e.g. 250.0"
                                    class="input-field"
                                    required
                                />
                            </div>

                            <div class="space-y-2 space-x-2">
                                <label for="bloom_time" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Bloom time {{ timeInputMode === 'seconds' ? '(seconds)' : '(mm:ss)' }}
                                </label>
                                <input
                                    v-if="timeInputMode === 'seconds'"
                                    id="bloom_time"
                                    v-model.number="newLog.bloom_time"
                                    type="number"
                                    min="0"
                                    placeholder="Optional, e.g. 30"
                                    class="input-field"
                                />
                                <div v-else class="grid grid-cols-[3.5rem_auto_1fr] items-center gap-2">
                                    <input
                                        id="bloom_time"
                                        :value="getTimeMinutes('bloom_time')"
                                        type="number"
                                        min="0"
                                        placeholder="2"
                                        class="input-field"
                                        @input="updateTimeFromClock('bloom_time', 'minutes', readClockInputValue($event))"
                                    />
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">:</span>
                                    <input
                                        :value="getTimeSeconds('bloom_time')"
                                        type="number"
                                        min="0"
                                        max="59"
                                        placeholder="30"
                                        class="input-field"
                                        @input="updateTimeFromClock('bloom_time', 'seconds', readClockInputValue($event))"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ timeInputMode === 'seconds' ? 'Optional' : 'Optional. Example: 2 minutes 30 seconds.' }}
                                </p>
                            </div>

                            <div class="space-y-2 space-x-2">
                                <label for="brew_time" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total brew time {{ timeInputMode === 'seconds' ? '(seconds)' : '(mm:ss)' }}
                                </label>
                                <input
                                    v-if="timeInputMode === 'seconds'"
                                    id="brew_time"
                                    v-model.number="newLog.brew_time"
                                    type="number"
                                    min="0"
                                    placeholder="e.g. 180"
                                    class="input-field"
                                    required
                                />
                                <div v-else class="grid grid-cols-[3.5rem_auto_1fr] items-center gap-2">
                                    <input
                                        id="brew_time"
                                        :value="getTimeMinutes('brew_time')"
                                        type="number"
                                        min="0"
                                        placeholder="3"
                                        class="input-field"
                                        @input="updateTimeFromClock('brew_time', 'minutes', readClockInputValue($event))"
                                    />
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">:</span>
                                    <input
                                        :value="getTimeSeconds('brew_time')"
                                        type="number"
                                        min="0"
                                        max="59"
                                        placeholder="00"
                                        class="input-field"
                                        @input="updateTimeFromClock('brew_time', 'seconds', readClockInputValue($event))"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ timeInputMode === 'seconds' ? 'Required. Enter total seconds.' : 'Required. Example: 2 minutes 30 seconds.' }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="brewRatio"
                            class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-100"
                        >
                            Current brew ratio: 1:{{ brewRatio }}
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Flavor notes</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                “Listed” means notes printed by the roaster. “Tasted” means notes you personally picked up in the cup.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <div class="mb-4 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                                    :class="
                                        selectedFlavorType === 'listed'
                                            ? 'bg-amber-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-amber-50 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'
                                    "
                                    @click="selectedFlavorType = 'listed'"
                                >
                                    Adding roaster notes
                                </button>
                                <button
                                    type="button"
                                    class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                                    :class="
                                        selectedFlavorType === 'tasted'
                                            ? 'bg-green-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-green-50 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700'
                                    "
                                    @click="selectedFlavorType = 'tasted'"
                                >
                                    Adding tasted notes
                                </button>
                            </div>

                            <div class="relative mb-3" @focusin="dropdownOpen = true" @focusout="dropdownOpen = false">
                                <label for="flavor_search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Search flavors to add as {{ selectedFlavorType === 'listed' ? 'roaster notes' : 'tasted notes' }}
                                </label>
                                <input
                                    id="flavor_search"
                                    v-model="flavorSearch"
                                    :placeholder="selectedFlavorType === 'listed' ? 'Search listed flavor notes...' : 'Search tasted flavor notes...'"
                                    class="input-field w-full"
                                />

                                <ul
                                    v-show="dropdownOpen && filteredFlavors.length"
                                    class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <li v-for="flavor in filteredFlavors" :key="flavor.id">
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @mousedown.prevent
                                            @click="addFlavor(flavor.id)"
                                        >
                                            <span class="text-gray-700 dark:text-gray-200">{{ flavor.flavor }}</span>
                                            <span
                                                class="rounded-full px-2 py-1 text-xs font-medium"
                                                :class="
                                                    selectedFlavorType === 'listed'
                                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200'
                                                        : 'bg-green-100 text-green-800 dark:bg-green-950/40 dark:text-green-200'
                                                "
                                            >
                                                Add
                                            </span>
                                        </button>
                                    </li>
                                </ul>

                                <div
                                    v-show="dropdownOpen && !filteredFlavors.length"
                                    class="absolute z-10 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    No flavors found.
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div class="space-y-3 rounded-xl bg-white p-4 dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-gray-800 dark:text-gray-100">Listed flavors</h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ listedFlavors.length }} selected</span>
                                    </div>

                                    <div v-if="listedFlavors.length" class="flex flex-wrap gap-2">
                                        <span
                                            v-for="flavor in listedFlavors"
                                            :key="`listed-${flavor.flavor_id}`"
                                            class="inline-flex items-center rounded-full bg-amber-500 px-3 py-1 text-sm text-white"
                                        >
                                            {{ flavorName(flavor.flavor_id) }}
                                            <button
                                                type="button"
                                                class="ml-2 text-xs text-white/90 hover:text-white"
                                                @click="removeFlavor(flavor.flavor_id, flavor.type)"
                                            >
                                                Remove
                                            </button>
                                        </span>
                                    </div>
                                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No roaster notes added yet.</p>
                                </div>

                                <div class="space-y-3 rounded-xl bg-white p-4 dark:bg-gray-800">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-gray-800 dark:text-gray-100">Tasted flavors</h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ tastedFlavors.length }} selected</span>
                                    </div>

                                    <div v-if="tastedFlavors.length" class="flex flex-wrap gap-2">
                                        <span
                                            v-for="flavor in tastedFlavors"
                                            :key="`tasted-${flavor.flavor_id}`"
                                            class="inline-flex items-center rounded-full bg-green-600 px-3 py-1 text-sm text-white"
                                        >
                                            {{ flavorName(flavor.flavor_id) }}
                                            <button
                                                type="button"
                                                class="ml-2 text-xs text-white/90 hover:text-white"
                                                @click="removeFlavor(flavor.flavor_id, flavor.type)"
                                            >
                                                Remove
                                            </button>
                                        </span>
                                    </div>
                                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No tasted notes added yet.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div
                        class="flex flex-col gap-3 border-t border-gray-200 pt-6 md:flex-row md:items-center md:justify-between dark:border-gray-700"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Required fields are enough to capture the recipe now; flavor notes can be added later.
                        </p>
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white transition-colors hover:bg-amber-600 md:w-auto"
                        >
                            Save brew log
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
