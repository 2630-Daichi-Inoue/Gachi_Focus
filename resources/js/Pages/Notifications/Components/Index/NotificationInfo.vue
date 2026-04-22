<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ViewNotificationModal from './ViewNotificationModal.vue'
import { formatDate, formatTime } from '@/utils/formatters'

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
    <div class="flex flex-row items-center justify-start md:gap-4 w-full">
        <div class="flex flex-col gap-1 w-full cursor-pointer hover:bg-gray-200 p-2 rounded"
            @click="showViewNotificationModal = true">
            <p class="text-gray-500 text-md">{{ formatDate(notification.created_at) }} {{ formatTime(notification.created_at) }}</p>
            <div>
                <span v-if="notification.related_type === 'user'">
                    Personal Notification
                </span>
                <span v-if="notification.related_type === 'space'">
                    Space Notification
                </span>
                <span v-if="notification.related_type === 'contact'">
                    Contact Notification
                </span>
                <span
                    v-if="notification.read_at === null"
                    class="text-red-500 ms-2 text-sm font-bold">
                    New!!!
                </span>
            </div>
            <h1 class="text-gray-700 text-xl font-bold">{{ notification.title }}</h1>
            <p class="text-black line-cramp-2">{{ notification.message }}</p>
        </div>
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
</template>
