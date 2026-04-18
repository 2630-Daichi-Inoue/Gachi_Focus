<script setup>
import {reactive, watch, computed} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import NotificationInfo from './Components/Index/NotificationInfo.vue'
import NotificationActions from './Components/Index/NotificationActions.vue'

const props = defineProps({
    notifications: Object,
    filters: Object,
})

const form = reactive({
    keyword: props.filters.keyword ?? '',
    newOnly: props.filters.newOnly ?? false,
    sort: props.filters.sort ?? 'datePresentToPast',
})

const search = () => {
    router.get(route('notifications.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

watch(() => form.sort, () => {
    router.get(route('notifications.index'), {
        keyword: props.filters.keyword ?? '',
        newOnly: form.newOnly ?? false,
        sort: form.sort,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
})

const clearFilters = () => {
    form.keyword = ''
    form.newOnly = false
    form.sort = 'datePresentToPast'
    search()
}

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Notifications Index" />

        <div class="m-4 max-w-6xl mx-auto">
            <!-- Title -->
            <div class="text-3xl font-bold mb-4">
                Notifications
            </div>
            <!-- Filters -->
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <!-- Checkbox for read/unread notifications -->
                    <div class="flex items-center gap-2 col-span-1">
                        <input v-model="form.keyword" type="text" placeholder="Search by keyword." class="border rounded px-3 py-2" />
                        <input type="checkbox" id="newOnly" v-model="form.newOnly" class="h-4 w-4 text-sky-600 border-gray-300 rounded">
                        <label for="newOnly" class="text-sm text-gray-700">New</label>
                    </div>

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
                        <option value="datePresentToPast">Date: Present → Past</option>
                        <option value="datePastToPresent">Date: Past → Present</option>
                    </select>
                    </div>

                </div>
            </form>

            <!-- Empty state -->
            <div v-if="notifications?.data?.length === 0" class="text-center mt-8">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">Try different filters or remove them.</p>
            </div>

            <!-- Notifications list -->
            <div v-else class="flex flex-col gap-4 mt-4">
                <div v-for="notification in notifications.data"
                    :key="notification.id"
                    class="md:w-full"
                >
                    <div class="h-full flex flex-col md:flex-row border-t border-gray-300 pt-4 gap-4">

                        <div class="w-full md:w-4/5 flex justify-center items-center">
                            <NotificationInfo :notification="notification" />
                        </div>
                        <div class="w-full md:w-1/5 flex justify-center items-center">
                            <NotificationActions :notification="notification" />
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center mt-6" v-if="notifications.data.length > 0">
                <p class="text-sm text-gray-500">
                    Showing {{ notifications.from }} to {{ notifications.to }} of {{ notifications.total }} results
                </p>
                <div class="flex gap-1">
                    <template v-for="link in notifications.links" :key="link.url ?? link.label">
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
