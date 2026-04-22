<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReviewForm from './Components/ReviewForm.vue'

const props = defineProps({
    reservation: Object,
    review: Object,
})

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit your review for ${props.reservation.space.name}`" />
        <div class="w-full">
            <ReviewForm :reservation="props.reservation"
                        :review="props.review"
                        />
        </div>
    </AuthenticatedLayout>
</template>
