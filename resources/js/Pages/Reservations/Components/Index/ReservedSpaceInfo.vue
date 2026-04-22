<script setup>

import { Link } from '@inertiajs/vue3'

const props = defineProps({
    reservation: Object,
})

</script>

<template>
    <div class="flex flex-col md:flex-row gap-4 w-full">
         <!-- Image + quick facts -->
        <div class="w-full md:w-1/2">
            <Link :href="`/spaces/${reservation.space.id}`">
                <img :src="reservation.space.image_path ? `/storage/${reservation.space.image_path}` : '/images/no-image.png'"
                    :alt="`space ${reservation.space.name}`"
                    class="w-full aspect-[4/3] object-cover rounded border border-gray-300"
                >
            </Link>
        </div>
        <div class="w-full md:w-1/2">
            <div class="text-lg md:text-xl font-semibold mb-2 min-w-0">
                <h1>
                    {{ reservation.space.name }}
                </h1>
            </div>

            <div class="mb-2">
                <p>
                    {{ reservation.space.full_address }}
                </p>
            </div>

            <!-- Google Map Link -->
            <div class="mb-2">
                <i class="fa-solid fa-location-dot mt-2"></i>
                <a :href="`https://www.google.com/maps?q=${encodeURIComponent(reservation.space.full_address)}`"
                    target="_blank"
                    class="font-medium text-blue-600 hover:underline">
                    View on Google Map >
                </a>
            </div>

        </div>
    </div>
</template>
