<script setup>
defineProps({
    space: Object,
    isFavorite: Boolean,
});
</script>

<template>
    <div
        class="flex items-center justify-between rounded-t-lg border-e border-s border-t border-gray-300 bg-sky-100 p-2"
    >
        <span>{{ space.name }}</span>
        <i v-if="isFavorite" class="fa-solid fa-star text-yellow-500"></i>
    </div>
</template>
