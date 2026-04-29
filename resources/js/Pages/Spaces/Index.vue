<script setup>
import {reactive, watch} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SpaceCardTitle from './Components/Index/SpaceCardTitle.vue'
import SpaceCardBody from './Components/Index/SpaceCardBody.vue'
import SpaceCardFooter from './Components/Index/SpaceCardFooter.vue'

const props = defineProps({
    spaces: Object,
    favoriteSpaceIds: Array,
    prefectures: Array,
    filters: Object,
})

const form = reactive({
    name: props.filters.name ?? '',
    prefecture: props.filters.prefecture ?? 'all',
    city: props.filters.city ?? '',
    address_line: props.filters.address_line ?? '',
    max_price: props.filters.max_price ?? '',
    sort: props.filters.sort ?? 'favorite_first',
    rows_per_page: props.filters.rows_per_page ?? 3,
})

const search = () => {
    router.get(route('spaces.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    form.name = ''
    form.prefecture = 'all'
    form.city = ''
    form.address_line = ''
    form.max_price = ''
    form.sort = 'favorite_first'
    form.rows_per_page = 3
    search()
}

watch(() => form.sort, () => { search() })
watch(() => form.rows_per_page, () => { search() })
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Spaces Index" />

        <!-- Search area -->
        <div class="p-4">
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
                    <input v-model="form.name" type="text" placeholder="Name" class="border rounded px-3 py-2" />
                    <select v-model="form.prefecture" name="prefecture" id="prefecture" class="border rounded px-3 py-2">
                        <option value="all">Select Prefecture</option>
                        <option v-for="prefecture in prefectures" :key="prefecture" :value="prefecture">{{ prefecture }}</option>
                    </select>
                    <input v-model="form.city" type="text" placeholder="City" class="border rounded px-3 py-2" />
                    <!-- <input v-model="form.address_line" type="text" placeholder="Address Line" class="border rounded px-3 py-2" /> -->

                    <div class="flex gap-2 col-span-1">
                        <button type="button" @click="clearFilters" class="border rounded px-3 py-2">
                            Clear Filters
                        </button>
                        <button type="submit" class="bg-slate-600 text-white rounded px-3 py-2">
                            Search
                        </button>
                    </div>

                    <div class="col-span-1"></div>

                    <div class="flex justify-end col-span-1">
                        <select v-model="form.sort" class="border rounded px-3 py-2 w-full">
                            <option value="favorite_first">Favorite First</option>
                            <option value="rating_high_to_low">Rating: High → Low</option>
                            <option value="price_high_to_low">Price: High → Low</option>
                            <option value="price_low_to_high">Price: Low → High</option>
                            <option value="capacity_high_to_low">Capacity: High → Low</option>
                            <option value="capacity_low_to_high">Capacity: Low → High</option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Empty state -->
            <div v-if="spaces?.data?.length === 0" class="text-center mt-8">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">Try different filters or remove them.</p>
            </div>

            <!-- Card list -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6 mt-6">
                <div v-for="space in spaces.data"
                    :key="space.id"
                    class="md:w-full mb-4"
                >
                    <div class="h-full flex flex-col">
                        <SpaceCardTitle :space="space" :isFavorite="favoriteSpaceIds.includes(space.id)" />
                        <SpaceCardBody :space="space" />
                        <SpaceCardFooter :space="space" />
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center" v-if="spaces.data.length > 0">
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Showing {{ spaces.from }} to {{ spaces.to }} of {{ spaces.total }} results
                    </p>
                    <div class="flex items-center gap-1">
                        <label class="text-sm text-gray-500">Rows:</label>
                        <select v-model="form.rows_per_page" class="border rounded pl-2 pr-7 py-1 text-sm">
                            <option :value="1">1</option>
                            <option :value="2">2</option>
                            <option :value="3">3</option>
                            <option :value="4">4</option>
                            <option :value="5">5</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-1">
                    <template v-for="link in spaces.links" :key="link.url ?? link.label">
                        <button
                            v-if="link.url"
                            @click="router.visit(link.url)"
                            v-html="link.label"
                            class="px-3 py-1 border rounded text-sm"
                            :class="{ 'bg-gray-200': link.active }"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
