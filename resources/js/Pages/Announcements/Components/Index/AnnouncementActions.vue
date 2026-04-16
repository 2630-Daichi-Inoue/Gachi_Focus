<script setup>
import { ref } from 'vue';
import ViewAnnouncementModal from './ViewAnnouncementModal.vue'

const props = defineProps({
    announcement: Object,
})

const showViewAnnouncementModal = ref(false);

</script>

<template>
    <div class="mx-2 flex flex-col gap-2 items-center w-full">
        <!-- View button -->
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
            <button @click="showViewAnnouncementModal = true"
                    class="btn p-4 bg-white flex items-center justify-center h-10 text-sky-500 font-bold border border-sky-500 rounded transition hover:bg-sky-200 disabled:opacity-50 disabled:cursor-not-allowed
                    disabled:hover:bg-white">
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
