<script setup>
import { reactive, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ContactInfo from "./Components/Index/ContactInfo.vue";
import ContactStatus from "./Components/Index/ContactStatus.vue";
import ContactActions from "./Components/Index/ContactActions.vue";

const props = defineProps({
    contacts: Object,
    filters: Object,
});

const form = reactive({
    contact_status: props.filters.contact_status ?? "all",
    sort: props.filters.sort ?? "datePresentToPast",
    rows_per_page: props.filters.rows_per_page ?? 20,
});

const search = () => {
    router.get(route("contacts.index"), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.contact_status = "all";
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
        <Head title="Contacts Index" />

        <div class="m-4 mx-auto max-w-6xl">
            <!-- Title -->
            <div class="mb-4 text-3xl font-bold">My Contacts</div>
            <!-- Filters -->
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
                    <select
                        v-model="form.contact_status"
                        class="rounded border px-3 py-2"
                    >
                        <option value="all">All</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
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
            <div v-if="contacts?.data?.length === 0" class="mt-8 text-center">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">
                    Try different filters or remove them.
                </p>
            </div>

            <!-- Contacts list -->
            <div v-else class="mt-4 flex flex-col gap-4">
                <div
                    v-for="contact in contacts.data"
                    :key="contact.id"
                    class="md:w-full"
                >
                    <div
                        class="flex h-full flex-col gap-4 border-t border-gray-300 pt-4 md:flex-row"
                    >
                        <div
                            class="flex w-full items-center justify-center md:w-1/2"
                        >
                            <ContactInfo :contact="contact" />
                        </div>
                        <div
                            class="flex w-full md:w-1/6 md:items-center md:justify-center"
                        >
                            <ContactStatus :contact="contact" />
                        </div>
                        <div
                            class="flex w-full items-center justify-center md:w-1/3"
                        >
                            <ContactActions :contact="contact" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="mt-6 flex items-center justify-between"
                v-if="contacts.data.length > 0"
            >
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Showing {{ contacts.from }} to {{ contacts.to }} of
                        {{ contacts.total }} results
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
                        v-for="link in contacts.links"
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
