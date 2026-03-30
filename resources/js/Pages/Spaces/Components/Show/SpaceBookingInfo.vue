<script setup>
import { Link } from '@inertiajs/vue3'
const props = defineProps({
    space: Object,
})
const formatPrice = (price) => {
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(price);
};
</script>

<template>
<div>
    <div class="border border-gray-300">
    <!-- Google Map -->
        <div>
        <iframe :src="`https://www.google.com/maps?q=${encodeURIComponent(space.full_address)}&output=embed`"
                class="w-full h-full border border-gray-300"
                allowfullscreen=""
                loading="lazy">
        </iframe>
        <div>
            <p>{{ space.full_address }}</p>
        </div>
        </div>
    </div>

    <!-- Google Map Link -->
    <div>
        <p class="map-address">
            <i class="fa-solid fa-location-dot mt-2 mr-2"></i>
            <Link :href="`https://www.google.com/maps?q=${encodeURIComponent(space.full_address)}`"
                target="_blank"
                class="font-medium text-blue-600 hover:underline">
                View on Google Map >
            </Link>
        </p>
    </div>

    <!-- Booking Info -->
     <div>
        <p class="font-medium text-xl">Price</p>
        <div class="flex gap-4 justify-normal mb-2">
            <!-- Weekday Price -->
            <div>
                <p>Weekday</p>
                <p>{{ formatPrice(space.weekday_price_yen) }} / 30 min.</p>
            </div>
            <div>
                <p>Weekend</p>
                <p>{{ formatPrice(space.weekend_price_yen) }} / 30 min.</p>
            </div>
        </div>
        <div>
            <Link :href="Fill_here_later"
                class="font-medium text-blue-600 hover:underline">
                Need to contact us? >
            </Link>
            <Link :href="`/spaces/${space.id}/book`"
                class="bg-sky-700 flex items-center justify-center h-10 w-1/2 mt-4 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
                Book it
            </Link>
        </div>
     </div>
</div>
</template>
