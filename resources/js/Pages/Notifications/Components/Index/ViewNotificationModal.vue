<script setup>
import { formatDate } from '@/utils/formatters'

const emit = defineEmits(['close', 'confirm']);

const props = defineProps({
    notification: Object,
})

</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center"
        @click.self="$emit('close')">
        <div class="relative bg-white p-4 w-full max-w-2xl rounded-lg shadow-lg">
            <button
                @click="$emit('close')"
                class="absolute top-3 right-3 px-2 py-1 text-gray-500 hover:text-black text-lg"
            >
                ✖︎
            </button>

            <div class="flex flex-col gap-2">
                <div class="flex flex-col justify-start gap-1">
                    <p class="text-2xl font-bold">{{ notification.title }}</p>
                    <p class="text-gray-500 text-md">{{ formatDate(notification.created_at) }}</p>
                </div>

                <p class="text-lg break-words">
                    {{ notification.message }}
                </p>

                <button
                v-if="notification.read_at === null"
                @click="$emit('confirm')"
                class="btn p-4 bg-white flex items-center justify-center h-10 text-sky-500 font-bold border border-sky-500 rounded transition hover:bg-sky-200"
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
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
