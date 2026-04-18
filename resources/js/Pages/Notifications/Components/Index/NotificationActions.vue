<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ViewNotificationModal from './ViewNotificationModal.vue'

const props = defineProps({
    notification: Object,
})

const showViewNotificationModal = ref(false);

const readNotification = () => {
    // Call the API to mark the notification as read
    router.patch(route('notifications.read', props.notification.id), {},  {
        preserveScroll: true,
        onSuccess: () => {
            showViewNotificationModal.value = false;
        },
        onError: () => {
            alert('Failed to mark the notification as read. Please try again.');
        },
    })
};

</script>

<template>
    <div class="mx-2 flex flex-col gap-2 items-center w-full">
        <!-- View button -->
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
            <button
                @click="showViewNotificationModal = true"
                class="btn p-4 bg-white flex items-center justify-center h-10 text-sky-500 font-bold border border-sky-500 rounded transition hover:bg-sky-200"
                    >
                View
            </button>
        </div>

        <!-- View notification modal -->
        <Transition name="modal-fade">
            <ViewNotificationModal
                v-if="showViewNotificationModal"
                :notification="notification"
                @close="showViewNotificationModal = false"
                @confirm="readNotification"
            />
        </Transition>
    </div>
</template>
