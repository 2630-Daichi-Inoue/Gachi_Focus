<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SpacePhoto from './Components/Show/SpacePhoto.vue'
import SpaceDetail from './Components/Show/SpaceDetail.vue'
import SpaceBookingInfo from './Components/Show/SpaceBookingInfo.vue'
defineProps({
    space: Object,
    reviewInfo: Object,
})

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="space.name" />
        <div class="flex flex-col md:flex-row m-4 gap-4">

            <div class="w-full md:w-1/3">
                <!-- Left Column -->
                <SpacePhoto :space="space" />
            </div>

            <div class="w-full md:w-1/3">
                <!-- Center Column -->
                <SpaceDetail :space="space" :reviewInfo="reviewInfo" />
            </div>

            <div class="w-full md:w-1/3">
                <!-- Right Column -->
                <SpaceBookingInfo :space="space" />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
