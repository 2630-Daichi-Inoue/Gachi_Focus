<script setup>
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SpacePhoto from './Components/Create/SpacePhoto.vue'
import ReservationForm from './Components/Create/ReservationForm.vue'

const props = defineProps({
    space: Object,
    startCandidates: Array,
    lastStartAt: String,
    date: String,
})

const selectedDate = ref(props.date)

watch(selectedDate, (newDate) => {
    router.get(
        route('reservations.create', props.space.id),
        { date: newDate },
        {
            preserveState: true,
            replace: true
        }
    )
})
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Book ${space.name}`" />

        <div class="flex flex-col md:flex-row m-4 gap-4">

            <!-- Left Column -->
            <div class="w-full md:w-1/2">
                <SpacePhoto :space="space" />
            </div>

            <!-- Right Column -->
            <div class="w-full md:w-1/2">
                <ReservationForm
                                :space="space"
                                :startCandidates="startCandidates"
                                v-model:selectedDate="selectedDate" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
