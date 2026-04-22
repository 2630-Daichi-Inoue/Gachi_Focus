<script setup>
import { formatDate, formatTime } from '@/utils/formatters'

const props = defineProps({
    contact: Object,
})

</script>

<template>
    <div class="flex flex-row items-center justify-start md:gap-4 w-full">
        <div class="flex flex-col gap-1 min-w-0">
            <p class="text-gray-500 text-md">{{ formatDate(contact.created_at) }} {{ formatTime(contact.created_at) }}</p>
            <h1 class="text-gray-700 text-xl font-bold">{{ contact.title }}</h1>
            <p class="text-black truncate">{{ contact.message }}</p>
        </div>
    </div>
</template>
