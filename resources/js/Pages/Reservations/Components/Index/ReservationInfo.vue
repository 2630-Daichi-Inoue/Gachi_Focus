<script setup>
import { formatDate, formatPrice, formatTime } from "@/utils/formatters";

const props = defineProps({
    reservation: Object,
});

const formatStatus = (status) => {
    const now = new Date();
    if (status === "pending") return "Pending Payment";
    if (status === "booked" && new Date(props.reservation.ended_at) < now)
        return "Completed";
    if (status === "booked") return "Booked";
    if (status === "canceled") return "Canceled";
    return "";
};
</script>

<template>
    <div class="flex w-full flex-row items-center justify-around md:gap-4">
        <div class="flex flex-col gap-1">
            <p>{{ formatDate(reservation.started_at) }}</p>
            <p>
                {{ formatTime(reservation.started_at) }} -
                {{ formatTime(reservation.ended_at) }}
            </p>
            <p>{{ formatPrice(reservation.total_price_yen) }}</p>
            <p>{{ reservation.quantity }} people</p>
        </div>
        <div class="md:flex md:justify-end">
            <div v-if="reservation.reservation_status === 'pending'">
                <p
                    class="w-auto rounded-full bg-yellow-400 px-2 py-1 text-center text-xl text-yellow-900"
                >
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div
                v-if="
                    reservation.reservation_status === 'booked' &&
                    new Date(reservation.ended_at) >= new Date()
                "
            >
                <p
                    class="w-auto rounded-full bg-sky-500 px-2 py-1 text-center text-xl text-sky-900"
                >
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div v-if="reservation.reservation_status === 'canceled'">
                <p
                    class="w-auto rounded-full bg-gray-500 px-2 py-1 text-center text-xl text-white"
                >
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div
                v-if="
                    reservation.reservation_status === 'booked' &&
                    new Date(reservation.ended_at) < new Date()
                "
            >
                <p
                    class="w-auto rounded-full bg-green-500 px-2 py-1 text-center text-xl text-green-900"
                >
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
        </div>
    </div>
</template>
