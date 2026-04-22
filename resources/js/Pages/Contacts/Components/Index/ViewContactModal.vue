<script setup>

const emit = defineEmits(['close']);

const props = defineProps({
    contact: Object,
})

</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center"
        @click.self="$emit('close')">
        <div class="relative bg-white p-4 w-2/3 rounded-lg shadow-lg">
            <button
                @click="$emit('close')"
                class="absolute top-3 right-3 px-2 py-1 text-gray-500 hover:text-black text-lg"
            >
                ✖︎
            </button>

            <div class="flex flex-col gap-2">
                <p class="text-2xl font-bold">
                    {{ contact.title }}
                </p>
                <p class="text-lg break-words">
                    {{ contact.message }}
                </p>
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
