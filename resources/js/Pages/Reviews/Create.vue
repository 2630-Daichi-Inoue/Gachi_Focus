<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReviewForm from './Components/ReviewForm.vue'

const props = defineProps({
    reservation: Object,
})

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Leave a review for ${props.reservation.space.name}`" />
        <div class="w-full">
            <ReviewForm :reservation="props.reservation"
        />
        </div>
    </AuthenticatedLayout>
</template>
