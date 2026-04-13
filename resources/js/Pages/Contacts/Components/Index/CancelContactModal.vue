<script setup>
const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center" @click.self="$emit('close')">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <p class="text-lg font-bold mb-4">
                Are you sure you want to cancel this contact?
            </p>
            <div class="flex justify-evenly gap-2 mt-4">
                <button @click="$emit('close')" class="p-4 btn bg-red-700 text-white font-bold border border-gray-300 rounded transition hover:bg-red-800 h-10 w-1/2 flex items-center justify-center">
                    No, keep it.
                </button>
                <button @click="$emit('confirm')" class="p-4 btn bg-sky-700 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800 h-10 w-1/2 flex items-center justify-center">
                    Yes, cancel it.
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
