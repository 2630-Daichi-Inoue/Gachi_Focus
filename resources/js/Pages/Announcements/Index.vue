<script setup>
import {reactive, watch, computed} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AnnouncementInfo from './Components/Index/AnnouncementInfo.vue'
import AnnouncementActions from './Components/Index/AnnouncementActions.vue'

const props = defineProps({
    announcements: Object,
    filters: Object,
})

const form = reactive({
    keyword: props.filters.keyword ?? '',
    sort: props.filters.sort ?? 'datePresentToPast',
})

const search = () => {
    router.get(route('announcements.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

watch(() => form.sort, () => {
    router.get(route('announcements.index'), {
        keyword: props.filters.keyword ?? '',
        sort: form.sort,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
})

const clearFilters = () => {
    form.keyword = ''
    form.sort = 'datePresentToPast'
    search()
}

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Announcements Index" />

        <div class="m-4 max-w-6xl mx-auto">
            <!-- Title -->
            <div class="text-3xl font-bold mb-4">
                Announcements
            </div>
            <!-- Filters -->
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <input v-model="form.keyword" type="text" placeholder="Search by keyword." class="border rounded px-3 py-2" />

                    <select v-model="form.sort" class="border rounded px-3 py-2">
                        <option value="datePresentToPast">Date: Present → Past</option>
                        <option value="datePastToPresent">Date: Past → Present</option>
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
            <div v-if="announcements?.data?.length === 0" class="text-center mt-8">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">Try different filters or remove them.</p>
            </div>

            <!-- Announcements list -->
            <div v-else class="flex flex-col gap-4 mt-4">
                <div v-for="announcement in announcements.data"
                    :key="announcement.id"
                    class="md:w-full"
                >
                    <div class="h-full flex flex-col md:flex-row border-t border-gray-300 pt-4 gap-4">

                        <div class="w-full md:w-4/5 flex justify-center items-center">
                            <AnnouncementInfo :announcement="announcement" />
                        </div>
                        <div class="w-full md:w-1/5 flex justify-center items-center">
                            <AnnouncementActions :announcement="announcement" />
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center mt-6" v-if="announcements.data.length > 0">
                <p class="text-sm text-gray-500">
                    Showing {{ announcements.from }} to {{ announcements.to }} of {{ announcements.total }} results
                </p>
                <div class="flex gap-1">
                    <template v-for="link in announcements.links" :key="link.url ?? link.label">
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
