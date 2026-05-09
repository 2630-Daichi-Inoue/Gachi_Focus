<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import ViewNotificationModal from "./ViewNotificationModal.vue";
import { formatDate, formatTime } from "@/utils/formatters";

const props = defineProps({
    notification: Object,
});

const showViewNotificationModal = ref(false);
const readError = ref("");

const readNotification = () => {
    router.patch(
        route("notifications.read", props.notification.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showViewNotificationModal.value = false;
                readError.value = "";
            },
            onError: () => {
                showViewNotificationModal.value = false;
                readError.value =
                    "Failed to mark the notification as read. Please try again.";
            },
        },
    );
};
</script>

<template>
    <div class="flex w-full flex-row items-center justify-start md:gap-4">
        <div
            class="flex w-full cursor-pointer flex-col gap-1 rounded p-2 hover:bg-gray-200"
            @click="showViewNotificationModal = true"
        >
            <p class="text-md text-gray-500">
                {{ formatDate(notification.created_at) }}
                {{ formatTime(notification.created_at) }}
            </p>
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
                    class="ms-2 text-sm font-bold text-red-500"
                >
                    New!!!
                </span>
            </div>
            <h1 class="text-xl font-bold text-gray-700">
                {{ notification.title }}
            </h1>
            <p class="line-cramp-2 text-black">{{ notification.message }}</p>
        </div>
    </div>

    <p v-if="readError" class="px-2 text-sm text-red-500">{{ readError }}</p>

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
