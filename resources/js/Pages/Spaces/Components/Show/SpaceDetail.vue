<script setup>
defineProps({
    space: Object,
    reviewInfo: Object,
})
</script>

<template>
<div class="space-y-6">
    <!-- Title + Rating -->
    <div>
        <h1 class="text-4xl font-bold">
            {{ space.name }}
        </h1>

        <div v-if="reviewInfo?.reviewCount > 0" class="flex items-center gap-1 mt-2">
            <div v-for="n in Math.floor(reviewInfo?.averageRating || 0)" :key="'full-' + n">
                <i class="fa-solid fa-star text-yellow-500"></i>
            </div>
            <div v-if="reviewInfo?.averageRating - Math.floor(reviewInfo?.averageRating || 0) >= 0.5">
                <i class="fa-solid fa-star-half-stroke text-yellow-500"></i>
            </div>
            <div v-for="n in 5 - Math.ceil(reviewInfo?.averageRating || 0)" :key="'empty-' + n">
                <i class="fa-regular fa-star text-yellow-500"></i>
            </div>
            <span class="ml-2 text-sm text-gray-600">
                {{ reviewInfo?.averageRating ? reviewInfo.averageRating.toFixed(1) : '-' }}
            </span>
        </div>
        <p v-else class="mt-2 text-gray-500">No reviews yet.</p>
    </div>

    <!-- Basic Info -->
    <div>
        <h2 class="text-2xl font-bold mb-2">Capacity</h2>
        <i class="text-2xl fa-solid fa-people-group mr-2"></i>
        <span class="text-2xl">{{ space.capacity }} people</span>
    </div>

    <div>
        <div v-if="space.amenities.length > 0">
            <p class="text-2xl font-bold mb-2">Amenities</p>
            <div class="flex flex-wrap gap-2 mb-2">
                <div
                    v-for="amenity in space.amenities"
                    :key="amenity.id"
                    class="px-3 py-1 bg-cyan-100 rounded border border-black text-sm inline-flex items-center">
                    {{ amenity.name }}
                </div>
            </div>
        </div>
        <div v-else>
            <p class="text-gray-500">No amenities listed.</p>
        </div>

        <!-- Description -->
        <h2 class="text-2xl font-bold mb-2">Description</h2>
        <p class="leading-relaxed text-gray-700">
            {{ space.description }}
        </p>
    </div>

</div>
</template>
