<script setup>
const props = defineProps({
    space: Object,
})
</script>

<template>
<div class="bg-sky-100 border-b border-s border-e border-gray-300 p-2 rounded-b-lg">
    <div class="flex flex-wrap gap-2">
        <template v-for="amenity in space.amenities" :key="amenity.id">
            <div class="bg-slate-100 text-black border border-gray-300 rounded p-1 text-sm">
                {{ amenity.name }}
            </div>
        </template>
        <template v-if="!space.amenities || space.amenities.length === 0">
            <div>No special amenities</div>
        </template>
    </div>
</div>
</template>
