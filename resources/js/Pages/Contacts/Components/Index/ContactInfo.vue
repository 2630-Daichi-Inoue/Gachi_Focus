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

const formatTime = (dateStr) => {
    const getTimePart = (dateStr) => {
        const date = new Date(dateStr);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    };
    const [hour, minute] = getTimePart(dateStr).split(':');
    const date = new Date();
    date.setHours(hour);
    date.setMinutes(minute);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

</script>

<template>
    <div class="flex flex-row items-center justify-start md:gap-4 w-full">
        <div class="flex flex-col gap-1 min-w-0">
            <p class="text-gray-500 text-xl">{{ formatDate(contact.created_at) }} {{ formatTime(contact.created_at) }}</p>
            <h1 class="text-gray-700 text-lg">{{ contact.title }}</h1>
            <p class="text-black truncate">{{ contact.message }}</p>
        </div>
    </div>
</template>
