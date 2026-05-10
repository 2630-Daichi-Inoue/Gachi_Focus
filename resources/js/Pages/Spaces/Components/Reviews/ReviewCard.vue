<script setup>
import { formatDate } from "@/utils/formatters";

const props = defineProps({
    review: Object,
});
</script>

<template>
    <div class="rounded border bg-white p-4">
        <h1 class="mb-2 text-xl font-semibold">
            <span v-if="review.user?.deleted_at" class="italic text-gray-400"
                >Deleted user</span
            >
            <span v-else>{{ review.user?.name }}</span>
            <span class="ml-2 text-sm font-normal text-gray-500">{{
                formatDate(review.created_at)
            }}</span>
        </h1>
        <div class="flex items-center gap-1">
            <!-- Filled Stars -->
            <div v-for="n in Math.floor(review.rating || 0)" :key="'full-' + n">
                <i class="fa-solid fa-star text-yellow-500"></i>
            </div>

            <!-- Empty Stars -->
            <div
                v-for="n in 5 - Math.ceil(review.rating || 0)"
                :key="'empty-' + n"
            >
                <i class="fa-regular fa-star text-yellow-500"></i>
            </div>
        </div>
        <p class="text-gray-600">{{ review.comment || "" }}</p>
    </div>
</template>
