<script setup>
import { Link } from '@inertiajs/vue3'
import { formatPrice, formatTimeStr } from '@/utils/formatters'

const props = defineProps({
    space: Object,
})

</script>

<template>
<div>
    <div>
    <!-- Google Map -->
        <div class="border border-gray-300 aspect-[16/9] md:aspect-[4/3] xl:aspect-square overflow-hidden">
            <iframe :src="`https://www.google.com/maps?q=${encodeURIComponent(space.full_address)}&output=embed`"
                    class="w-full h-full"
                    allowfullscreen=""
                    loading="lazy">
            </iframe>
        </div>
        <div class="border-s border-e border-b border-gray-300">
            <p>{{ space.full_address }}</p>
        </div>
    </div>

    <!-- Google Map Link -->
    <div class="mb-2">
        <p>
            <i class="fa-solid fa-location-dot mt-2 mr-2"></i>
            <a :href="`https://www.google.com/maps?q=${encodeURIComponent(space.full_address)}`"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-blue-600 hover:underline">
                View on Google Map >
            </a>
        </p>
    </div>

    <!-- Booking Info -->
     <div>
        <div class="mb-2">
            <h1 class="font-medium text-xl">Opening Hours</h1>
            <p>{{ formatTimeStr(space.open_time) }} - {{ formatTimeStr(space.close_time) }}</p>
        </div>

        <div class="flex flex-col gap-2 justify-normal mb-2">
            <h1 class="font-medium text-xl">Price</h1>
            <div class="flex flex-wrap gap-4">
                <div>
                    <p>Weekday</p>
                    <p>{{ formatPrice(space.weekday_price_yen) }} / 30 min.</p>
                    </div>

                    <div>
                    <p>Weekend</p>
                    <p>{{ formatPrice(space.weekend_price_yen) }} / 30 min.</p>
                </div>
            </div>
        </div>
        <div>
            <Link :href="route('contacts.create', { reservation_id: null })"
                class="font-medium text-blue-600 hover:underline">
                Need to contact us? >
            </Link>
            <div class="flex flex-col md:flex-row gap-2 mt-4">
                <Link :href="`/spaces`"
                    class="flex items-center justify-center md:w-1/2 text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                    Go Back
                </Link>
                <Link :href="route('reservations.create', { space: space.id })"
                    class="p-2 font-bold text-3xl flex items-center justify-center md:w-1/2 text-white border border-gray-500 rounded transition bg-cyan-600 hover:bg-cyan-700">
                    Book it
                </Link>
            </div>
        </div>
     </div>
</div>
</template>
