<script setup>
import {reactive, watch, computed} from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ContactInfo from './Components/Index/ContactInfo.vue'
import ContactStatus from './Components/Index/ContactStatus.vue'
import ContactActions from './Components/Index/ContactActions.vue'

const props = defineProps({
    contacts: Object,
    filters: Object,
})

const form = reactive({
    contactStatus: props.filters.contactStatus ?? 'all',
    sort: props.filters.sort ?? 'datePresentToPast',
})

const search = () => {
    router.get(route('contacts.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const appliedContactStatus = computed(() => props.filters.contactStatus ?? 'all')

watch(() => form.sort, () => {
    router.get(route('contacts.index'), {
        contactStatus: appliedContactStatus.value,
        sort: form.sort,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
})

const clearFilters = () => {
    form.contactStatus = 'all'
    form.sort = 'datePresentToPast'
    search()
}

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Contacts Index" />

        <div class="text-3xl font-bold ms-4 mt-4">
            My Contacts
        </div>

        <!-- Search area -->
        <div class="m-4">
            <form @submit.prevent="search" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <select v-model="form.contactStatus" class="border rounded px-3 py-2">
                        <option value="all">All</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        <option value="canceled">Canceled</option>
                    </select>
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
            <div v-if="contacts?.data?.length === 0" class="text-center mt-8">
                <h3 class="text-xl font-semibold">No results.</h3>
                <p class="text-gray-500">Try different filters or remove them.</p>
            </div>

            <!-- Contacts list -->
            <div v-else class="flex flex-col gap-4 mt-4">
                <div v-for="contact in contacts.data"
                    :key="contact.id"
                    class="md:w-full"
                >
                    <div class="h-full flex flex-col md:flex-row border-t border-gray-300 pt-4 gap-4">

                        <div class="w-full md:w-1/3 flex justify-center items-center">
                            <ContactInfo :contact="contact" />
                        </div>
                        <div class="w-full md:w-1/3 flex justify-center items-center">
                            <ContactStatus :contact="contact" />
                        </div>
                        <div class="w-full md:w-1/3 flex justify-center items-center">
                            <ContactActions :contact="contact" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center mt-6" v-if="contacts.data.length > 0">
                <p class="text-sm text-gray-500">
                    Showing {{ contacts.from }} to {{ contacts.to }} of {{ contacts.total }} results
                </p>
                <div class="flex gap-1">
                    <template v-for="link in contacts.links" :key="link.url ?? link.label">
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
