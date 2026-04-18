<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    space: Object,
    isFavorite: Boolean,
    reviewInfo: Object,
})

const favorited = ref(props.isFavorite)
const isBouncing = ref(false)
const isSubmitting = ref(false)

const toggleFavorite = () => {
    if (isBouncing.value || isSubmitting.value) return

    isBouncing.value = true
    isSubmitting.value = true
    setTimeout(() => { isBouncing.value = false }, 300)

    if (favorited.value) {
        router.delete(route('favorites.destroy', props.space.id), {
            preserveState: true,
            onSuccess: () => { favorited.value = false },
            onFinish: () => { isSubmitting.value = false },
        })
    } else {
        router.post(route('favorites.store', props.space.id), {}, {
            preserveState: true,
            onSuccess: () => { favorited.value = true },
            onFinish: () => { isSubmitting.value = false },
        })
    }
}

</script>

<template>
<div class="space-y-6">
    <!-- Title + Rating -->
    <div>
        <div class="mb-2 flex items-center gap-4">
            <span class="text-3xl font-bold">
                {{ space.name }}
            </span>
            <button
                @click="toggleFavorite"
                :aria-label="favorited ? 'Remove from favorites' : 'Add to favorites'"
                :title="favorited ? 'Remove from favorites' : 'Add to favorites'"
                :disabled="isBouncing || isSubmitting"
                :class="[
                    'transition-transform',
                    isBouncing ? 'scale-150' : 'scale-100',
                    isSubmitting ? 'opacity-50' : ''
                ]"
                style="transition-duration: 300ms;">
                <i v-if="favorited" class="fa-solid fa-star text-xl text-yellow-500"></i>
                <i v-else class="fa-regular fa-star text-xl"></i>
            </button>
        </div>

        <div class="mb-2">
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

                        <p class="ml-2 text-sm text-gray-600">
                            {{ reviewInfo?.averageRating ? reviewInfo.averageRating.toFixed(1) : '-' }} ({{ reviewInfo?.reviewCount || 0 }} reviews)
                        </p>
                    </div>
                    <div>
                        <Link :href="route('spaces.reviewIndex', space)"
                            class="font-medium text-blue-600 hover:underline">
                            Show reviews >
                        </Link>
                    </div>
        </div>
    </div>

    <!-- Basic Info -->
    <div>
        <div class="mb-2">
            <h2 class="text-2xl font-bold">Capacity</h2>
            <i class="text-2xl fa-solid fa-people-group mr-2"></i>
            <span class="text-2xl">{{ space.capacity }} people</span>
        </div>

        <div class="mb-2">
            <h2 class="text-2xl font-bold">Amenities</h2>
            <div v-if="space.amenities.length > 0">
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
        </div>

        <!-- Description -->
        <h2 class="text-2xl font-bold">Description</h2>
        <p class="leading-relaxed text-gray-700">
            {{ space.description }}
        </p>
    </div>

</div>
</template>
