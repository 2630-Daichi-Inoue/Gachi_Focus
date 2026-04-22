<script setup>

const props = defineProps({
    space: Object,
    reviewInfo: Object,
})

</script>

<template>
<div class="p-4">
    <h2 class="text-2xl font-bold mb-4">
        {{ space.name }}
    </h2>
    <div class="flex items-center gap-4">
        <div class="text-4xl font-bold">
            {{ reviewInfo?.averageRating ? reviewInfo.averageRating.toFixed(1) : '-' }}
        </div>
        <div>
            <div class="flex items-center gap-1">
                <!-- Filled Stars -->
                <div v-for="n in Math.floor(reviewInfo?.averageRating || 0)" :key="'full-' + n">
                    <i class="fa-solid fa-star text-yellow-500"></i>
                </div>

                <!-- Half Star -->
                <div v-if="reviewInfo?.averageRating - Math.floor(reviewInfo?.averageRating || 0) >= 0.5">
                    <i class="fa-solid fa-star-half-stroke text-yellow-500"></i>
                </div>

                <!-- Empty Stars -->
                <div v-for="n in 5 - Math.ceil(reviewInfo?.averageRating || 0)" :key="'empty-' + n">
                    <i class="fa-regular fa-star text-yellow-500"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600">
                {{ reviewInfo?.reviewCount || 0 }} reviews
            </p>
        </div>
    </div>
</div>
</template>
