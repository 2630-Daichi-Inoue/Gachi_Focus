<script setup>

const props = defineProps({
    contact: Object,
})

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate();
    return `${month}/${day}/${year}`;
}

</script>

<template>
    <div class="flex flex-row items-center justify-around md:gap-4 w-full">
        <div class="flex flex-col gap-1">
            <p class="text-gray-500 text-xl">{{ formatDate(contact.created_at) }}</p>
            <h1 class="text-gray-700 text-lg">{{ contact.title }}</h1>
            <p class="text-black">{{ contact.message }}</p>
        </div>
    </div>
</template>
