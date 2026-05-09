<script setup>
import { reactive, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReservationInfo from "./Components/Index/ReservationInfo.vue";
import ReservedSpaceInfo from "./Components/Index/ReservedSpaceInfo.vue";
import ReservationActions from "./Components/Index/ReservationActions.vue";

const props = defineProps({
    reservations: Object,
    filters: Object,
});

const form = reactive({
    name: props.filters.name ?? "",
    reservation_status: props.filters.reservation_status ?? "all",
    sort: props.filters.sort ?? "date_future_to_past",
    rows_per_page: props.filters.rows_per_page ?? 20,
});

const search = () => {
    router.get(route("reservations.index"), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.name = "";
    form.reservation_status = "all";
    form.sort = "date_future_to_past";
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
        <Head title="Reservations Index" />

        <div class="m-4 mx-auto max-w-6xl">
            <!-- Title -->
            <div class="mb-4 text-3xl font-bold">My Reservations</div>
            <!-- Filters -->
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Space's Name"
                        class="rounded border px-3 py-2"
                    />
                    <select
                        v-model="form.reservation_status"
                        class="rounded border px-3 py-2"
                    >
                        <option value="all">All</option>
                        <option value="pending">Pending Payment</option>
                        <option value="booked">Booked or Completed</option>
                        <option value="canceled">Canceled</option>
                    </select>

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

                    <div class="col-span-1 flex justify-end">
                        <select
                            v-model="form.sort"
                            class="w-full rounded border px-3 py-2"
                        >
                            <option value="date_future_to_past">
                                Date: Future → Past
                            </option>
                            <option value="date_past_to_future">
                                Date: Past → Future
                            </option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Empty state -->
            <div
                v-if="reservations?.data?.length === 0"
                class="mt-8 text-center"
            >
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">
                    Try different filters or remove them.
                </p>
            </div>

            <!-- Reservations list -->
            <div v-else class="mt-4 flex flex-col gap-4">
                <div
                    v-for="reservation in reservations.data"
                    :key="reservation.id"
                    class="md:w-full"
                >
                    <div
                        class="flex h-full flex-col border-t border-gray-300 pt-4 md:flex-row"
                    >
                        <div class="flex w-full md:w-1/2">
                            <ReservedSpaceInfo :reservation="reservation" />
                        </div>
                        <div
                            class="flex w-full items-center justify-center md:w-1/4"
                        >
                            <ReservationInfo :reservation="reservation" />
                        </div>
                        <div
                            class="flex w-full items-center md:w-1/4 md:justify-start lg:justify-end"
                        >
                            <ReservationActions :reservation="reservation" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="mt-6 flex items-center justify-between"
                v-if="reservations.data.length > 0"
            >
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Showing {{ reservations.from }} to
                        {{ reservations.to }} of
                        {{ reservations.total }} results
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
                        v-for="link in reservations.links"
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
