<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReservationInfo from "./Components/Payment/ReservationInfo.vue";
import PaymentForm from "./Components/Payment/PaymentForm.vue";

const props = defineProps({
    space: Object,
    reservationData: Object,
    conflictingReservations: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Pay for ${space.name}`" />

        <div class="m-4 flex flex-col gap-4 md:flex-row">
            <!-- Left Column -->
            <div class="w-full md:w-1/2">
                <ReservationInfo
                    :space="space"
                    :reservationData="reservationData"
                />
            </div>

            <!-- Right Column -->
            <div class="w-full md:w-1/2">
                <PaymentForm
                    :space="space"
                    :reservationData="reservationData"
                    :conflictingReservations="conflictingReservations"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
