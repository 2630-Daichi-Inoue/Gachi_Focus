<script setup>

const emit = defineEmits(['close', 'confirm']);

const props = defineProps({
    contact: Object,
})

</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center" @click.self="$emit('close')">
        <div class="bg-white p-4 w-1/2 rounded-lg shadow-lg">
            <div class="flex flex-col">
                <div class="w-full flex justify-end">
                    <button @click="$emit('close')" class="px-2 py-1 text-black text-sm font-bold ">
                        ✖︎
                    </button>
                </div>
                <p class="text-lg font-bold break-words">
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
