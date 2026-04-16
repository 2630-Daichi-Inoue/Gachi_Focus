<script setup>
import { ref } from 'vue';
import ViewAnnouncementModal from './ViewAnnouncementModal.vue'

const props = defineProps({
    announcement: Object,
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

const showViewAnnouncementModal = ref(false);

</script>

<template>
    <div class="flex flex-row items-center justify-start md:gap-4 w-full">
        <div class="flex flex-col gap-1 w-full cursor-pointer hover:bg-gray-200 p-2 rounded"
            @click="showViewAnnouncementModal = true">
            <p class="text-gray-500 text-md">{{ formatDate(announcement.published_at) }} {{ formatTime(announcement.published_at) }}</p>
            <h1 class="text-gray-700 text-xl font-bold">{{ announcement.title }}</h1>
            <p class="text-black line-cramp-2">{{ announcement.message }}</p>
        </div>
    </div>

    <!-- View announcement modal -->
    <Transition name="modal-fade">
        <ViewAnnouncementModal
            v-if="showViewAnnouncementModal"
            :announcement="props.announcement"
            @close="showViewAnnouncementModal = false"
        />
    </Transition>
</template>
