<script setup>
import { formatPrice } from "@/utils/formatters";

const props = defineProps({
    space: Object,
    reservationData: Object,
});
</script>

<template>
    <div class="border border-gray-300 bg-white p-4">
        <h1 class="text-2xl text-gray-500">Space's Name</h1>
        <p class="mb-2 text-2xl font-bold">{{ space.name }}</p>

        <h1 class="text-2xl text-gray-500">Address</h1>
        <p class="mb-2 text-2xl font-bold">{{ space.full_address }}</p>

        <h1 class="text-2xl text-gray-500">Date & Time</h1>
        <p class="mb-2 text-2xl font-bold">
            {{ reservationData.date }} {{ reservationData.started_at }} -
            {{ reservationData.ended_at }}
        </p>

        <h1 class="text-2xl text-gray-500">Quantity</h1>
        <p class="mb-2 text-2xl font-bold">{{ reservationData.quantity }}</p>

        <h1 class="text-2xl text-gray-500">Total Price</h1>
        <p class="mb-2 text-2xl font-bold">
            {{ formatPrice(reservationData.total_price_yen) }}
        </p>

        <img
            :src="
                space.image_path
                    ? `/storage/${space.image_path}`
                    : '/images/no-image.png'
            "
            :alt="`space ${space.name}`"
            class="w-96 rounded border border-gray-300 object-fill"
        />
    </div>
</template>
