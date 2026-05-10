<script setup>
import { reactive, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Summary from "./Components/Reviews/Summary.vue";
import ReviewCard from "./Components/Reviews/ReviewCard.vue";

const props = defineProps({
    space: Object,
    reviewInfo: Object,
    filters: Object,
});

const form = reactive({
    stars: props.filters.stars ?? "all",
    sort: props.filters.sort ?? "rating_high_to_low",
});

const search = () => {
    router.get(route("spaces.reviewIndex", props.space), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.stars = "all";
    form.sort = "rating_high_to_low";
    search();
};

watch(
    () => form.sort,
    () => {
        search();
    },
);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Space's Reviews" />
        <div class="flex flex-col md:flex-row">
            <!-- Left column -->
            <div class="h-full w-1/5 p-4">
                <Summary :space="space" :reviewInfo="reviewInfo" />
            </div>

            <!-- Right column -->
            <!-- Search area -->
            <div class="h-full w-4/5 p-4">
                <div v-if="reviewInfo.reviewCount > 0">
                    <form @submit.prevent="search" class="space-y-4">
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-6">
                            <select
                                v-model="form.stars"
                                name="stars"
                                id="stars"
                                class="h-10 rounded border px-3 py-2"
                            >
                                <option value="all">Select Stars</option>
                                <option
                                    v-for="star in [5, 4, 3, 2, 1]"
                                    :key="star"
                                    :value="star"
                                >
                                    {{ "★".repeat(star) }}
                                </option>
                            </select>

                            <div class="col-span-1 flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="clearFilters"
                                    class="h-10 whitespace-nowrap rounded border px-3"
                                >
                                    Clear Filters
                                </button>
                                <button
                                    type="submit"
                                    class="h-10 whitespace-nowrap rounded bg-slate-600 px-3 text-white"
                                >
                                    Search
                                </button>
                            </div>

                            <div class="col-span-3"></div>

                            <div class="col-span-1 flex justify-end">
                                <select
                                    v-model="form.sort"
                                    class="h-10 w-full rounded border px-3 py-2"
                                >
                                    <option value="rating_high_to_low">
                                        Rating: High → Low
                                    </option>
                                    <option value="rating_low_to_high">
                                        Rating: Low → High
                                    </option>
                                    <option value="newest">Newest First</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Empty state -->
                    <div
                        v-if="reviewInfo.filteredReviews.data.length === 0"
                        class="mt-8 text-center"
                    >
                        <h3 class="text-xl font-semibold">No results.</h3>
                        <p class="text-gray-500">
                            Try different filters or remove them.
                        </p>
                    </div>

                    <div v-else class="mt-6 flex flex-col">
                        <div
                            v-for="review in reviewInfo.filteredReviews.data"
                            :key="review.id"
                            class="mb-4 md:w-full"
                        >
                            <div class="h-full">
                                <ReviewCard :review="review" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-between"
                        v-if="reviewInfo.filteredReviews.data.length > 0"
                    >
                        <p class="text-sm text-gray-500">
                            Showing {{ reviewInfo.filteredReviews.from }} to
                            {{ reviewInfo.filteredReviews.to }} of
                            {{ reviewInfo.filteredReviews.total }} results
                        </p>
                        <div class="flex gap-1">
                            <template
                                v-for="link in reviewInfo.filteredReviews.links"
                                :key="link.url ?? link.label"
                            >
                                <button
                                    v-if="link.url"
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                    class="rounded border px-3 py-1 text-sm"
                                    :class="{ 'bg-gray-200': link.active }"
                                />
                            </template>
                        </div>
                    </div>
                </div>
                <div v-else class="mt-8 text-center">
                    <h3 class="text-xl font-semibold">No reviews yet.</h3>
                    <p class="text-gray-500">
                        We'd be glad if you could be the first one to review
                        this space!
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
