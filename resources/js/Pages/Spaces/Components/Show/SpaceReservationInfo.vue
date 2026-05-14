<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { formatPrice, formatTimeStr } from "@/utils/formatters";

const props = defineProps({
    space: Object,
});

const isRestricted = usePage().props.auth.user.user_status === "restricted";
</script>

<template>
    <div>
        <div>
            <!-- Google Map -->
            <div
                class="aspect-[16/9] overflow-hidden border border-gray-300 md:aspect-[4/3]"
            >
                <iframe
                    :src="`https://www.google.com/maps?q=${encodeURIComponent(space.full_address)}&output=embed`"
                    class="h-full w-full"
                    allowfullscreen=""
                    loading="lazy"
                >
                </iframe>
            </div>
            <div class="border-b border-e border-s border-gray-300">
                <p>{{ space.full_address }}</p>
            </div>
        </div>

        <!-- Google Map Link -->
        <div class="mb-2">
            <p>
                <i class="fa-solid fa-location-dot mr-2 mt-2"></i>
                <a
                    :href="`https://www.google.com/maps?q=${encodeURIComponent(space.full_address)}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-blue-600 hover:underline"
                >
                    View on Google Map >
                </a>
            </p>
        </div>

        <!-- Booking Info -->
        <div>
            <div class="mb-2">
                <h1 class="text-xl font-medium">Opening Hours</h1>
                <p>
                    {{ formatTimeStr(space.open_time) }} -
                    {{ formatTimeStr(space.close_time) }}
                </p>
            </div>

            <div class="mb-2 flex flex-col justify-normal gap-2">
                <h1 class="text-xl font-medium">Price</h1>
                <div class="flex flex-wrap gap-4">
                    <div>
                        <p>Weekday</p>
                        <p>
                            {{ formatPrice(space.weekday_price_yen) }} / 30 min.
                        </p>
                    </div>

                    <div>
                        <p>Weekend</p>
                        <p>
                            {{ formatPrice(space.weekend_price_yen) }} / 30 min.
                        </p>
                    </div>
                </div>
            </div>
            <div>
                <Link
                    :href="route('contacts.create', { reservation_id: null })"
                    class="font-medium text-blue-600 hover:underline"
                >
                    Need to contact us? >
                </Link>
                <div class="mt-4 flex flex-col gap-2 md:flex-row">
                    <Link
                        :href="`/spaces`"
                        class="flex items-center justify-center rounded border border-gray-500 p-2 text-3xl text-black transition hover:bg-gray-200 md:w-1/2"
                    >
                        Go Back
                    </Link>
                    <Link
                        v-if="!isRestricted"
                        :href="
                            route('reservations.create', { space: space.id })
                        "
                        class="flex items-center justify-center rounded border border-gray-500 bg-cyan-600 p-2 text-3xl font-bold text-white transition hover:bg-cyan-700 md:w-1/2"
                    >
                        Book it
                    </Link>
                    <span
                        v-else
                        class="flex cursor-not-allowed items-center justify-center rounded border border-gray-300 bg-gray-300 p-2 text-3xl font-bold text-white md:w-1/2"
                    >
                        Book it
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
