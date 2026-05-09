<script setup>
import { reactive, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AnnouncementInfo from "./Components/Index/AnnouncementInfo.vue";
import AnnouncementActions from "./Components/Index/AnnouncementActions.vue";

const props = defineProps({
    announcements: Object,
    filters: Object,
});

const form = reactive({
    keyword: props.filters.keyword ?? "",
    sort: props.filters.sort ?? "datePresentToPast",
    rows_per_page: props.filters.rows_per_page ?? 20,
});

const search = () => {
    router.get(route("announcements.index"), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.keyword = "";
    form.sort = "datePresentToPast";
    form.rows_per_page = 20;
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
        <Head title="Announcements Index" />

        <div class="m-4 mx-auto max-w-6xl">
            <!-- Title -->
            <div class="mb-4 text-3xl font-bold">Announcements</div>
            <!-- Filters -->
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
                    <input
                        v-model="form.keyword"
                        type="text"
                        placeholder="Search by keyword."
                        class="rounded border px-3 py-2"
                    />

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
                            <option value="datePresentToPast">
                                Date: Present → Past
                            </option>
                            <option value="datePastToPresent">
                                Date: Past → Present
                            </option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Empty state -->
            <div
                v-if="announcements?.data?.length === 0"
                class="mt-8 text-center"
            >
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">
                    Try different filters or remove them.
                </p>
            </div>

            <!-- Announcements list -->
            <div v-else class="mt-4 flex flex-col gap-4">
                <div
                    v-for="announcement in announcements.data"
                    :key="announcement.id"
                    class="md:w-full"
                >
                    <div
                        class="flex h-full flex-col gap-4 border-t border-gray-300 pt-4 md:flex-row"
                    >
                        <div
                            class="flex w-full items-center justify-center md:w-4/5"
                        >
                            <AnnouncementInfo :announcement="announcement" />
                        </div>
                        <div
                            class="flex w-full items-center justify-center md:w-1/5"
                        >
                            <AnnouncementActions :announcement="announcement" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="mt-6 flex items-center justify-between"
                v-if="announcements.data.length > 0"
            >
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Showing {{ announcements.from }} to
                        {{ announcements.to }} of
                        {{ announcements.total }} results
                    </p>
                    <div class="flex items-center gap-1">
                        <label class="text-sm text-gray-500">Rows:</label>
                        <select
                            v-model="form.rows_per_page"
                            class="rounded border py-1 pl-2 pr-7 text-sm"
                        >
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-1">
                    <template
                        v-for="link in announcements.links"
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
