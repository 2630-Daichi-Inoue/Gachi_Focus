<script setup>
import { formatDate } from '@/utils/formatters'

const props = defineProps({
    review: Object,
})

</script>

<template>
<div class="p-4 border rounded bg-white">
    <h1 class="text-xl font-semibold mb-2">{{ review.user.name || 'No Title' }} <span class="text-gray-500 text-sm">{{ formatDate(review.created_at) }}</span></h1>
    <div class="flex items-center gap-1">
        <!-- Filled Stars -->
        <div v-for="n in Math.floor(review.rating || 0)" :key="'full-' + n">
            <i class="fa-solid fa-star text-yellow-500"></i>
        </div>
        
        <!-- Empty Stars -->
        <div v-for="n in 5 - Math.ceil(review.rating || 0)" :key="'empty-' + n">
            <i class="fa-regular fa-star text-yellow-500"></i>
        </div>
    </div>
    <p class="text-gray-600">{{ review.comment || '' }}</p>
</div>
</template>
