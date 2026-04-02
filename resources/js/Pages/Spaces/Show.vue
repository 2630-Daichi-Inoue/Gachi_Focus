<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SpacePhoto from './Components/Show/SpacePhoto.vue'
import SpaceDetail from './Components/Show/SpaceDetail.vue'
import SpaceBookingInfo from './Components/Show/SpaceReservationInfo.vue'
defineProps({
    space: Object,
    reviewInfo: Object,
})

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="space.name" />

        <div class="flex flex-col md:flex-row m-4 gap-4">

            <!-- Left Column -->
            <div class="w-full md:w-1/3">
                <SpacePhoto :space="space" />
            </div>

            <!-- Middle Column -->
            <div class="w-full md:w-1/3">
                <SpaceDetail :space="space" :reviewInfo="reviewInfo" />
            </div>

            <!-- Right Column -->
            <div class="w-full md:w-1/3">
                <SpaceBookingInfo :space="space" />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
