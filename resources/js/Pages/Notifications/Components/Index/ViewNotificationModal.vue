<script setup>
import { formatDate } from "@/utils/formatters";

const emit = defineEmits(["close", "confirm"]);

const props = defineProps({
    notification: Object,
});
</script>

<template>
    <div
        class="fixed inset-0 flex items-center justify-center bg-black/50"
        @click.self="$emit('close')"
    >
        <div
            class="relative w-full max-w-2xl rounded-lg bg-white p-4 shadow-lg"
        >
            <button
                @click="$emit('close')"
                class="absolute right-3 top-3 px-2 py-1 text-lg text-gray-500 hover:text-black"
            >
                ✖︎
            </button>

            <div class="flex flex-col gap-2">
                <div class="flex flex-col justify-start gap-1">
                    <p class="text-2xl font-bold">{{ notification.title }}</p>
                    <p class="text-md text-gray-500">
                        {{ formatDate(notification.created_at) }}
                    </p>
                </div>

                <p class="break-words text-lg">
                    {{ notification.message }}
                </p>

                <button
                    v-if="notification.read_at === null"
                    @click="$emit('confirm')"
                    class="btn flex h-10 items-center justify-center rounded border border-sky-500 bg-white p-4 font-bold text-sky-500 transition hover:bg-sky-200"
                >
                    Mark as read.
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>
