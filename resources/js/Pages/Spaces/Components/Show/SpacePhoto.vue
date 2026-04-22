<script setup>
defineProps({
    space: Object,
})
</script>

<template>
<div class="bg-white border border-gray-300 h-96 md:h-86 relative">
    <img :src="space.image_path ? `/storage/${space.image_path}` : '/images/no-image.png'"
        :alt="`space ${space.name}`"
        class="w-full h-full object-fit"
    >
</div>
</template>
