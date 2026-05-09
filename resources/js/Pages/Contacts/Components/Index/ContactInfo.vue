<script setup>
import { formatDate, formatTime } from "@/utils/formatters";

const props = defineProps({
    contact: Object,
});
</script>

<template>
    <div class="flex w-full flex-row items-center justify-start md:gap-4">
        <div class="flex min-w-0 flex-col gap-1">
            <p class="text-md text-gray-500">
                {{ formatDate(contact.created_at) }}
                {{ formatTime(contact.created_at) }}
            </p>
            <h1 class="text-xl font-bold text-gray-700">{{ contact.title }}</h1>
            <p class="truncate text-black">{{ contact.message }}</p>
        </div>
    </div>
</template>
