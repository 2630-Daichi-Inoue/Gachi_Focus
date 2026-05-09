<script setup>
import { Link } from "@inertiajs/vue3";
import { formatPrice, formatTimeStr } from "@/utils/formatters";

const props = defineProps({
    space: Object,
});
</script>

<template>
    <div class="border border-gray-300 bg-slate-100">
        <div class="mt-2 flex gap-2 px-2">
            <!-- Image + quick facts -->
            <div class="w-1/2">
                <img
                    :src="
                        space.image_path
                            ? `/storage/${space.image_path}`
                            : '/images/no-image.png'
                    "
                    :alt="`space ${space.name}`"
                    class="h-48 w-full rounded border border-gray-300 object-cover"
                />
            </div>
            <div class="grid w-1/2 grid-cols-2 gap-y-1">
                <p class="col-span-2 mb-1">
                    {{ space.city }}, {{ space.prefecture }}
                </p>
                <span>Hours:</span>
                <span
                    >{{ formatTimeStr(space.open_time) }} -
                    {{ formatTimeStr(space.close_time) }}</span
                >
                <span>Seats:</span>
                <span>{{ space.capacity }}</span>
                <span>Weekday:</span>
                <span>{{ formatPrice(space.weekday_price_yen) }} / 0.5h</span>
                <span>Weekend:</span>
                <span>{{ formatPrice(space.weekend_price_yen) }} / 0.5h</span>
                <span>Rating:</span>
                <span
                    >★{{
                        space.public_reviews_avg_rating
                            ? Number(space.public_reviews_avg_rating).toFixed(1)
                            : "-"
                    }}</span
                >
            </div>
        </div>
        <div class="my-2 flex justify-around gap-2 px-2">
            <Link
                :href="`/spaces/${space.id}`"
                class="mt-4 flex h-10 flex-1 items-center justify-center rounded border border-gray-300 text-xl text-black transition hover:bg-gray-200"
            >
                View details
            </Link>
            <!-- the link below will be modified later -->
            <Link
                :href="route('reservations.create', space.id)"
                class="mt-4 flex h-10 flex-1 items-center justify-center rounded border border-gray-300 bg-sky-700 text-xl font-bold text-white transition hover:bg-sky-800"
            >
                Book it
            </Link>
        </div>
    </div>
</template>
