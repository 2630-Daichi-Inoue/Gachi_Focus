<script setup>
import { ref } from "vue";
import ViewAnnouncementModal from "./ViewAnnouncementModal.vue";
import { formatDate, formatTime } from "@/utils/formatters";

const props = defineProps({
    announcement: Object,
});

const showViewAnnouncementModal = ref(false);
</script>

<template>
    <div class="flex w-full flex-row items-center justify-start md:gap-4">
        <div
            class="flex w-full cursor-pointer flex-col gap-1 rounded p-2 hover:bg-gray-200"
            @click="showViewAnnouncementModal = true"
        >
            <p class="text-md text-gray-500">
                {{ formatDate(announcement.published_at) }}
                {{ formatTime(announcement.published_at) }}
            </p>
            <h1 class="text-xl font-bold text-gray-700">
                {{ announcement.title }}
            </h1>
            <p class="line-cramp-2 text-black">{{ announcement.message }}</p>
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
