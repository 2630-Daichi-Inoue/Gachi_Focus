<script setup>
import { formatPrice } from '@/utils/formatters'

const props = defineProps({
    space: Object,
    reservationData: Object,
})

</script>

<template>
<div class="bg-white border border-gray-300 p-4">

        <h1 class="text-2xl text-gray-500">Space's Name</h1>
        <p class="font-bold text-2xl mb-2">{{ space.name }}</p>

        <h1 class="text-2xl text-gray-500">Address</h1>
        <p class="font-bold text-2xl mb-2">{{ space.full_address }}</p>

        <h1 class="text-2xl text-gray-500">Date & Time</h1>
        <p class="font-bold text-2xl mb-2">{{ reservationData.date }} {{ reservationData.started_at }} - {{ reservationData.ended_at }}</p>

        <h1 class="text-2xl text-gray-500">Quantity</h1>
        <p class="font-bold text-2xl mb-2">{{ reservationData.quantity }}</p>
        
        <h1 class="text-2xl text-gray-500">Total Price</h1>
        <p class="font-bold text-2xl mb-2">{{ formatPrice(reservationData.total_price_yen) }}</p>

        <img :src="space.image_path ? `/storage/${space.image_path}` : '/images/no-image.png'"
        :alt="`space ${space.name}`"
        class="w-96 object-fill border border-gray-300 rounded"
        >

</div>
</template>
