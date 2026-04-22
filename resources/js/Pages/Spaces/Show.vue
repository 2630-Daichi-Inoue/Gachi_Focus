<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SpacePhoto from './Components/Show/SpacePhoto.vue'
import SpaceDetail from './Components/Show/SpaceDetail.vue'
import SpaceReservationInfo from './Components/Show/SpaceReservationInfo.vue'
defineProps({
    space: Object,
    isFavorite: Boolean,
    reviewInfo: Object,
})
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="space.name" />

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 m-4">
            <div>
                <SpacePhoto :space="space" />
            </div>

            <div>
                <SpaceDetail :space="space"
                            :isFavorite="isFavorite"
                            :reviewInfo="reviewInfo"
                />
            </div>

            <div class="md:col-span-2 xl:col-span-1">
                <SpaceReservationInfo :space="space" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
