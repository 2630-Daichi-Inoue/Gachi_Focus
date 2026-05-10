<script setup>
defineProps({
    space: Object,
});
</script>

<template>
    <div class="relative border border-gray-300 bg-white">
        <img
            :src="
                space.image_path
                    ? `/storage/${space.image_path}`
                    : '/images/no-image.png'
            "
            :alt="`space ${space.name}`"
            class="w-full object-fill"
        />
        <div class="absolute inset-0 mx-2 mt-2">
            <div
                class="text-shadow-lg inline-block rounded bg-slate-500 bg-opacity-50 px-2 py-1 text-3xl font-bold text-white"
            >
                {{ space.name }}
            </div>
        </div>
    </div>
</template>
