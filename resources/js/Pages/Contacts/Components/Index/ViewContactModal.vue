<script setup>
const emit = defineEmits(["close"]);

const props = defineProps({
    contact: Object,
});
</script>

<template>
    <div
        class="fixed inset-0 flex items-center justify-center bg-black/50"
        @click.self="$emit('close')"
    >
        <div class="relative w-2/3 rounded-lg bg-white p-4 shadow-lg">
            <button
                @click="$emit('close')"
                class="absolute right-3 top-3 px-2 py-1 text-lg text-gray-500 hover:text-black"
            >
                ✖︎
            </button>

            <div class="flex flex-col gap-2">
                <p class="text-2xl font-bold">
                    {{ contact.title }}
                </p>
                <p class="break-words text-lg">
                    {{ contact.message }}
                </p>
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
