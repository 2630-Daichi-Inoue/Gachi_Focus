<script setup>
const props = defineProps({
    space: Object,
});
</script>

<template>
    <div
        class="rounded-b-lg border-b border-e border-s border-gray-300 bg-sky-100 p-2"
    >
        <div class="flex flex-wrap gap-2">
            <template v-for="amenity in space.amenities" :key="amenity.id">
                <div
                    class="rounded border border-gray-300 bg-slate-100 p-1 text-sm text-black"
                >
                    {{ amenity.name }}
                </div>
            </template>
            <template v-if="!space.amenities || space.amenities.length === 0">
                <div>No special amenities</div>
            </template>
        </div>
    </div>
</template>
