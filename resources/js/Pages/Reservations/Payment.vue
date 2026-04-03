<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ReservationInfo from './Components/Payment/ReservationInfo.vue'
import PaymentForm from './Components/Payment/PaymentForm.vue'

const props = defineProps({
    space: Object,
    reservationData: Object,
})

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Pay for ${space.name}`" />

        <div class="flex flex-col md:flex-row m-4 gap-4">

            <!-- Left Column -->
            <div class="w-full md:w-1/2">
                <ReservationInfo
                            :space="space"
                            :reservationData="reservationData" />
            </div>

            <!-- Right Column -->
            <div class="w-full md:w-1/2">
                <PaymentForm
                            :space="space"
                            :reservationData="reservationData" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
