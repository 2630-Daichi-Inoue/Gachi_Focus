<script setup>
import { Link } from '@inertiajs/vue3'
import { formatPrice, formatTimeStr } from '@/utils/formatters'

const props = defineProps({
    space: Object,
})

</script>

<template>
<div class="bg-slate-100 border border-gray-300">
     <div class="flex gap-2 mt-2 px-2">
         <!-- Image + quick facts -->
        <div class="w-1/2">
            <img :src="space.image_path ? `/storage/${space.image_path}` : '/images/no-image.png'"
                :alt="`space ${space.name}`"
                class="w-full h-48 object-cover rounded border border-gray-300"
            >
        </div>
        <div class="w-1/2 grid grid-cols-2 gap-y-1">
            <p class="col-span-2 mb-1">{{ space.city }}, {{ space.prefecture }}</p>
            <span>Hours:</span>
            <span>{{ formatTimeStr(space.open_time) }} - {{ formatTimeStr(space.close_time) }}</span>
            <span>Seats:</span>
            <span>{{ space.capacity }}</span>
            <span>Weekday:</span>
            <span>{{ formatPrice(space.weekday_price_yen) }} / 0.5h</span>
            <span>Weekend:</span>
            <span>{{ formatPrice(space.weekend_price_yen) }} / 0.5h</span>
            <span>Rating:</span>
            <span>★{{ space.public_reviews_avg_rating ? Number(space.public_reviews_avg_rating).toFixed(1) : '-' }}</span>
        </div>
    </div>
    <div class="flex gap-2 my-2 px-2 justify-around">
        <Link :href="`/spaces/${space.id}`"
            class="text-xl flex items-center justify-center h-10 flex-1 mt-4 text-black border border-gray-300 rounded transition hover:bg-gray-200">
            View details
        </Link>
        <!-- the link below will be modified later -->
        <Link :href="route('reservations.create', space.id)"
            class="text-xl bg-sky-700 flex items-center justify-center h-10 flex-1 mt-4 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
            Book it
        </Link>
    </div>
</div>
</template>
