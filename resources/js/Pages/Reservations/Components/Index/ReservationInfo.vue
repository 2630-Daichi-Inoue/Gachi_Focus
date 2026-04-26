<script setup>
import { formatDate, formatPrice, formatTime } from '@/utils/formatters'

const props = defineProps({
    reservation: Object,
})

const formatStatus = (status) => {
    const now = new Date();
    if (status === 'pending') return 'Pending Payment';
    if (status === 'booked' && new Date(props.reservation.ended_at) < now) return 'Completed';
    if (status === 'booked') return 'Booked';
    if (status === 'canceled') return 'Canceled';
    return '';
};

</script>

<template>
    <div class="flex flex-row items-center justify-around md:gap-4 w-full">
        <div class="flex flex-col gap-1">
            <p>{{ formatDate(reservation.started_at) }}</p>
            <p>{{ formatTime(reservation.started_at) }} - {{ formatTime(reservation.ended_at) }}</p>
            <p>{{ formatPrice(reservation.total_price_yen) }}</p>
            <p>{{ reservation.quantity }} people</p>
        </div>
        <div class="md:flex md:justify-end">
            <div v-if="reservation.reservation_status === 'pending'">
                <p class="bg-yellow-400 text-yellow-900 text-xl px-2 py-1 rounded-full w-auto text-center">
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div v-if="reservation.reservation_status === 'booked' && new Date(reservation.ended_at) >= new Date()">
                <p class="bg-sky-500 text-sky-900 text-xl px-2 py-1 rounded-full w-auto text-center">
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div v-if="reservation.reservation_status === 'canceled'">
                <p class="bg-gray-500 text-white text-xl px-2 py-1 rounded-full w-auto text-center">
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div v-if="reservation.reservation_status === 'booked' && new Date(reservation.ended_at) < new Date()">
                <p class="bg-green-500 text-green-900 text-xl px-2 py-1 rounded-full w-auto text-center">
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
        </div>
    </div>
</template>
