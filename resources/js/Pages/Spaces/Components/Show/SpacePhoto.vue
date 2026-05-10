<script setup>
defineProps({
    space: Object,
});
</script>

<template>
    <div class="md:h-86 relative h-96 border border-gray-300 bg-white">
        <img
            :src="
                space.image_path
                    ? `/storage/${space.image_path}`
                    : '/images/no-image.png'
            "
            :alt="`space ${space.name}`"
            class="object-fit h-full w-full"
        />
    </div>
</template>
