<script setup>
defineProps({
    space: Object,
    isFavorite: Boolean,
})
</script>

<template>
<div class="bg-sky-100 border-t border-s border-e border-gray-300 p-2 rounded-t-lg flex items-center justify-between">
    <span>{{ space.name }}</span>
    <i v-if="isFavorite" class="fa-solid fa-star text-yellow-500"></i>
</div>
</template>
