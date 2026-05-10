<script setup>
const emit = defineEmits(["close", "confirm"]);
</script>

<template>
    <div
        class="fixed inset-0 flex items-center justify-center bg-black/50"
        @click.self="$emit('close')"
    >
        <div class="w-2/3 rounded-lg bg-white p-4 shadow-lg">
            <p class="mb-4 text-lg font-bold">
                Are you sure you want to cancel this contact?
            </p>
            <div class="mt-4 flex justify-evenly gap-2">
                <button
                    @click="$emit('close')"
                    class="btn flex h-10 w-1/2 items-center justify-center rounded border border-gray-300 bg-red-700 p-4 font-bold text-white transition hover:bg-red-800"
                >
                    No, keep it.
                </button>
                <button
                    @click="$emit('confirm')"
                    class="btn flex h-10 w-1/2 items-center justify-center rounded border border-gray-300 bg-sky-700 p-4 font-bold text-white transition hover:bg-sky-800"
                >
                    Yes, cancel it.
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
