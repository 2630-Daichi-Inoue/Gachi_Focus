<script setup>
import { ref } from "vue";
import ViewAnnouncementModal from "./ViewAnnouncementModal.vue";

const props = defineProps({
    announcement: Object,
});

const showViewAnnouncementModal = ref(false);
</script>

<template>
    <div class="mx-2 flex w-full flex-col items-center gap-2">
        <!-- View button -->
        <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
            <button
                @click="showViewAnnouncementModal = true"
                class="btn flex h-10 items-center justify-center rounded border border-sky-500 bg-white p-4 font-bold text-sky-500 transition hover:bg-sky-200 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white"
            >
                View
            </button>
        </div>

        <!-- View announcement modal -->
        <Transition name="modal-fade">
            <ViewAnnouncementModal
                v-if="showViewAnnouncementModal"
                :announcement="props.announcement"
                @close="showViewAnnouncementModal = false"
            />
        </Transition>
    </div>
</template>
