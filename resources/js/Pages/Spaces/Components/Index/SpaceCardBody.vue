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
<div class="bg-slate-100 border border-gray-300">
     <div class="flex gap-2 mt-2 px-2">
         <!-- Image + quick facts -->
        <div class="w-1/2">
            <img :src="space.image_path ? `/storage/${space.image_path}` : '/images/no-image.png'"
                :alt="`space ${space.name}`"
                class="object-cover rounded border border-gray-300"
            >
        </div>
        <div class="w-1/2">
            <p class="mb-1">
                {{ space.city }}, {{ space.prefecture }}
            </p>

            <p class="mb-1">
                Weekday:
                ¥{{ formatPrice(space.weekday_price_yen) }} / 0.5h
            </p>

            <p class="mb-1">
                Weekend:
                ¥{{ formatPrice(space.weekend_price_yen) }} / 0.5h
            </p>

            <p class="mb-1">
                Capacity: {{ space.capacity }}
            </p>

            <p class="mb-1">
                Rating: ★{{ space.public_reviews_avg_rating ? Number(space.public_reviews_avg_rating).toFixed(1) : '-' }}
            </p>
        </div>
    </div>
    <div class="flex gap-2 my-2 px-2 justify-around">
        <Link :href="`/spaces/${space.id}`"
            class="flex items-center justify-center h-10 w-1/4 mt-4 text-black border border-gray-300 rounded transition hover:bg-gray-200">
            View details
        </Link>
        <!-- the link below will be modified later -->
        <Link :href="`/spaces/${space.id}`"
            class="bg-sky-700 flex items-center justify-center h-10 w-1/4 mt-4 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
            Book it
        </Link>
    </div>
</div>
</template>
