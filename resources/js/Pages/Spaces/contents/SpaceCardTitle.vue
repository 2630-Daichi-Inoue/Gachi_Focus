<script setup>
const props = defineProps({
    space: Object,
})
</script>

<template>
<div class="bg-sky-100 border-t border-s border-e border-gray-300 p-2 rounded-t-lg">
    {{ space.name }}
</div>
</template>
