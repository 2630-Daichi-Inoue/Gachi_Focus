<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import ViewNotificationModal from "./ViewNotificationModal.vue";

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
    <div class="mx-2 flex w-full flex-col items-center gap-2">
        <!-- View button -->
        <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
            <button
                @click="showViewNotificationModal = true"
                class="btn flex h-10 items-center justify-center rounded border border-sky-500 bg-white p-4 font-bold text-sky-500 transition hover:bg-sky-200"
            >
                View
            </button>
        </div>

        <p v-if="readError" class="text-sm text-red-500">{{ readError }}</p>

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
