<script setup>
defineProps({
    space: Object,
})
</script>

<template>
<div class="bg-white border border-gray-300 overflow-hidden h-96 md:h-80 relative">
    <img :src="space.image_path ? `/storage/${space.image_path}` : '/images/no-image.png'"
        :alt="`space ${space.name}`"
        class="w-full h-full object-cover"
    >
    <div class="absolute inset-0 mx-2 mt-2">
        <div class="font-bold text-3xl text-white text-shadow-lg bg-slate-500 bg-opacity-50 px-2 py-1 rounded">
            {{ space.name }}
        </div>
    </div>
</div>
</template>
