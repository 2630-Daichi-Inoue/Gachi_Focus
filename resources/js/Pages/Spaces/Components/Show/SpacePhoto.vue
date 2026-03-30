<script setup>
defineProps({
    space: Object,
})
</script>

<template>
<div class="bg-white border border-gray-300 overflow-hidden h-96 md:h-80">
    <img :src="space.image_path ? `/storage/${space.image_path}` : '/images/no-image.png'"
        :alt="`space ${space.name}`"
        class="w-full h-full object-cover"
    >
</div>
</template>
