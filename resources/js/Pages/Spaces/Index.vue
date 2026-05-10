<script setup>
import { reactive, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SpaceCardTitle from "./Components/Index/SpaceCardTitle.vue";
import SpaceCardBody from "./Components/Index/SpaceCardBody.vue";
import SpaceCardFooter from "./Components/Index/SpaceCardFooter.vue";

const props = defineProps({
    spaces: Object,
    favoriteSpaceIds: Array,
    prefectures: Array,
    filters: Object,
});

const form = reactive({
    name: props.filters.name ?? "",
    prefecture: props.filters.prefecture ?? "all",
    city: props.filters.city ?? "",
    address_line: props.filters.address_line ?? "",
    max_price: props.filters.max_price ?? "",
    sort: props.filters.sort ?? "favorite_first",
    rows_per_page: props.filters.rows_per_page ?? 3,
});

const search = () => {
    router.get(route("spaces.index"), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.name = "";
    form.prefecture = "all";
    form.city = "";
    form.address_line = "";
    form.max_price = "";
    form.sort = "favorite_first";
    form.rows_per_page = 3;
    search();
};

watch(
    () => form.sort,
    () => {
        search();
    },
);
watch(
    () => form.rows_per_page,
    () => {
        search();
    },
);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Spaces Index" />

        <!-- Search area -->
        <div class="p-4">
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-6">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Name"
                        class="rounded border px-3 py-2"
                    />
                    <select
                        v-model="form.prefecture"
                        name="prefecture"
                        id="prefecture"
                        class="rounded border px-3 py-2"
                    >
                        <option value="all">Select Prefecture</option>
                        <option
                            v-for="prefecture in prefectures"
                            :key="prefecture"
                            :value="prefecture"
                        >
                            {{ prefecture }}
                        </option>
                    </select>
                    <input
                        v-model="form.city"
                        type="text"
                        placeholder="City"
                        class="rounded border px-3 py-2"
                    />
                    <!-- <input v-model="form.address_line" type="text" placeholder="Address Line" class="border rounded px-3 py-2" /> -->

                    <div class="col-span-1 flex gap-2">
                        <button
                            type="button"
                            @click="clearFilters"
                            class="rounded border px-3 py-2"
                        >
                            Clear Filters
                        </button>
                        <button
                            type="submit"
                            class="rounded bg-slate-600 px-3 py-2 text-white"
                        >
                            Search
                        </button>
                    </div>

                    <div class="col-span-1"></div>

                    <div class="col-span-1 flex justify-end">
                        <select
                            v-model="form.sort"
                            class="w-full rounded border px-3 py-2"
                        >
                            <option value="favorite_first">
                                Favorite First
                            </option>
                            <option value="rating_high_to_low">
                                Rating: High → Low
                            </option>
                            <option value="price_high_to_low">
                                Price: High → Low
                            </option>
                            <option value="price_low_to_high">
                                Price: Low → High
                            </option>
                            <option value="capacity_high_to_low">
                                Capacity: High → Low
                            </option>
                            <option value="capacity_low_to_high">
                                Capacity: Low → High
                            </option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Empty state -->
            <div v-if="spaces?.data?.length === 0" class="mt-8 text-center">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">
                    Try different filters or remove them.
                </p>
            </div>

            <!-- Card list -->
            <div
                v-else
                class="mb-6 mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2 2xl:grid-cols-3"
            >
                <div v-for="space in spaces.data" :key="space.id">
                    <div class="flex h-full flex-col">
                        <SpaceCardTitle
                            :space="space"
                            :isFavorite="favoriteSpaceIds.includes(space.id)"
                        />
                        <SpaceCardBody :space="space" />
                        <SpaceCardFooter :space="space" />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex items-center justify-between"
                v-if="spaces.data.length > 0"
            >
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Showing {{ spaces.from }} to {{ spaces.to }} of
                        {{ spaces.total }} results
                    </p>
                    <div class="flex items-center gap-1">
                        <label class="text-sm text-gray-500">Per page:</label>
                        <select
                            v-model="form.rows_per_page"
                            class="rounded border py-1 pl-2 pr-7 text-sm"
                        >
                            <option :value="3">3</option>
                            <option :value="6">6</option>
                            <option :value="9">9</option>
                            <option :value="12">12</option>
                            <option :value="15">15</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-1">
                    <template
                        v-for="link in spaces.links"
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
    </AuthenticatedLayout>
</template>
