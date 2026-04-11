<script setup>

import {reactive, watch} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Summary from './Components/Reviews/Summary.vue'
import ReviewCard from './Components/Reviews/ReviewCard.vue'

const props = defineProps({
    space: Object,
    reviewInfo: Object,
    filters: Object,
})

const form = reactive({
    stars: props.filters.stars ?? 'all',
    sort: props.filters.sort ?? 'rating_high_to_low',
})

const search = () => {
    router.get(route('spaces.reviewIndex', props.space), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    form.stars = 'all'
    form.sort = 'rating_high_to_low'
    search()
}

watch(() => form.sort, () => {
    search()
})

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Space's Reviews" />
        <div class="flex flex-col md:flex-row">
            <!-- Left column -->
            <div class="p-4 w-1/5 h-full">
                <Summary :space="space" :reviewInfo="reviewInfo" />
            </div>

            <!-- Right column -->
            <!-- Search area -->
            <div class="p-4 w-4/5 h-full">
                <div v-if="reviewInfo.reviewCount > 0">
                    <form @submit.prevent="search" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
                            <select v-model="form.stars" name="stars" id="stars" class="border rounded px-3 py-2">
                                <option value="all">Select Stars</option>
                                <option v-for="star in [5, 4, 3, 2, 1]" :key="star" :value="star">
                                    {{ '★'.repeat(star) }}
                                </option>
                            </select>
                            <select v-model="form.sort" class="border rounded px-3 py-2">
                                <option value="rating_high_to_low">Rating: High → Low</option>
                                <option value="rating_low_to_high">Rating: Low → High</option>
                                <option value="newest">Newest First</option>
                            </select>

                            <div class="flex gap-2">
                                <button type="button" @click="clearFilters" class="border rounded px-3 py-2">
                                    Clear Filters
                                </button>
                                <button type="submit" class="bg-slate-600 text-white rounded px-3 py-2">
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Empty state -->
                    <div v-if="reviewInfo.filteredReviews.data.length === 0" class="text-center mt-8">
                        <h3 class="text-xl font-semibold">No results.</h3>
                        <p class="text-gray-500">Try different filters or remove them.</p>
                    </div>

                    <div v-else class="flex flex-col mt-6">
                        <div v-for="review in reviewInfo.filteredReviews.data"
                            :key="review.id"
                            class="md:w-full mb-4"
                        >
                            <div class="h-full">
                                <ReviewCard :review="review" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-between items-center" v-if="reviewInfo.filteredReviews.data.length > 0">
                        <p class="text-sm text-gray-500">
                            Showing {{ reviewInfo.filteredReviews.from }} to {{ reviewInfo.filteredReviews.to }} of {{ reviewInfo.filteredReviews.total }} results
                        </p>
                        <div class="flex gap-1">
                            <template v-for="link in reviewInfo.filteredReviews.links" :key="link.url ?? link.label">
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
                <div v-else class="text-center mt-8">
                    <h3 class="text-xl font-semibold">No reviews yet.</h3>
                    <p class="text-gray-500">We'd be glad if you could be the first one to review this space!</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
