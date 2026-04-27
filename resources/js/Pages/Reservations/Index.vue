<script setup>
import {reactive, watch} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ReservationInfo from './Components/Index/ReservationInfo.vue'
import ReservedSpaceInfo from './Components/Index/ReservedSpaceInfo.vue'
import ReservationActions from './Components/Index/ReservationActions.vue'

const props = defineProps({
    reservations: Object,
    filters: Object,
})

const form = reactive({
    name: props.filters.name ?? '',
    reservation_status: props.filters.reservation_status ?? 'all',
    sort: props.filters.sort ?? 'date_future_to_past',
    rows_per_page: props.filters.rows_per_page ?? 20,
})

const search = () => {
    router.get(route('reservations.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    form.name = ''
    form.reservation_status = 'all'
    form.sort = 'date_future_to_past'
    form.rows_per_page = 20
    search()
}

watch(() => form.sort, () => { search() })
watch(() => form.rows_per_page, () => { search() })
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Reservations Index" />

        <div class="m-4 max-w-6xl mx-auto">
            <!-- Title -->
            <div class="text-3xl font-bold mb-4">
                My Reservations
            </div>
            <!-- Filters -->
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <input v-model="form.name" type="text" placeholder="Space's Name" class="border rounded px-3 py-2" />
                    <select v-model="form.reservation_status" class="border rounded px-3 py-2">
                        <option value="all">All</option>
                        <option value="pending">Pending Payment</option>
                        <option value="booked">Booked or Completed</option>
                        <option value="canceled">Canceled</option>
                    </select>

                    <div class="flex gap-2 col-span-1">
                        <button type="button" @click="clearFilters" class="border rounded px-3 py-2">
                            Clear Filters
                        </button>
                        <button type="submit" class="bg-slate-600 text-white rounded px-3 py-2">
                            Search
                        </button>
                    </div>

                    <div class="flex justify-end col-span-1">
                        <select v-model="form.sort" class="border rounded px-3 py-2 w-full">
                            <option value="date_future_to_past">Date: Future → Past</option>
                            <option value="date_past_to_future">Date: Past → Future</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Empty state -->
            <div v-if="reservations?.data?.length === 0" class="text-center mt-8">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">Try different filters or remove them.</p>
            </div>

            <!-- Reservations list -->
            <div v-else class="flex flex-col gap-4 mt-4">
                <div v-for="reservation in reservations.data"
                    :key="reservation.id"
                    class="md:w-full"
                >
                    <div class="h-full flex flex-col md:flex-row border-t border-gray-300 pt-4">

                        <div class="w-full md:w-1/2 flex">
                            <ReservedSpaceInfo :reservation="reservation" />
                        </div>
                        <div class="w-full md:w-1/4 flex justify-center items-center">
                            <ReservationInfo :reservation="reservation" />
                        </div>
                        <div class="w-full md:w-1/4 flex items-center md:justify-start lg:justify-end">
                            <ReservationActions :reservation="reservation" />
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center mt-6" v-if="reservations.data.length > 0">
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">
                        Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} results
                    </p>
                    <div class="flex items-center gap-1">
                        <label class="text-sm text-gray-500">Rows:</label>
                        <select v-model="form.rows_per_page" class="border rounded pl-2 pr-7 py-1 text-sm">
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-1">
                    <template v-for="link in reservations.links" :key="link.url ?? link.label">
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
