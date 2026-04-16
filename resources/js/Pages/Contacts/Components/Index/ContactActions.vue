<script setup>
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue';
import CancelContactModal from './CancelContactModal.vue'
import ViewContactModal from './ViewContactModal.vue'

const props = defineProps({
    contact: Object,
})

const isOpen = computed(() => props.contact.contact_status === 'open');

const isUnread = computed(() => props.contact.read_at === null);

const isClosed = computed(() => props.contact.contact_status === 'closed');

const isCanceled = computed(() => props.contact.contact_status === 'canceled');

const canCancel = computed(() => isOpen.value && isUnread.value);

const showViewContactModal = ref(false);

const showCancelContactModal = ref(false);

const cancelContact = () => {
    // Call the API to cancel the contact
    router.patch(route('contacts.cancel', props.contact.id), {},  {
        preserveScroll: true,
        onSuccess: () => {
            showCancelContactModal.value = false;
        },
        onError: () => {
            alert('Failed to cancel the contact. Please try again.');
        },
    })
};

const getMessage = computed(() => {
    if (isOpen.value && isUnread.value) {
        return 'We have not checked this contact yet. You can cancel it if you want.';
    }
    if (isOpen.value && !isUnread.value) {
        return 'We have checked this contact. Please wait for the response.';
    }
    if (isClosed.value) {
        return 'This contact has been closed. Please contact us again if you have any questions.';
    }
    if (isCanceled.value) {
        return 'This contact has been canceled by the user.';
    }
    return '';
});

</script>

<template>
    <div class="mx-2 flex flex-col gap-2 items-center w-full">
        <!-- Buttons -->
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
            <button @click="showViewContactModal = true"
                    class="btn p-4 bg-white flex items-center justify-center h-10 text-sky-500 font-bold border border-sky-500 rounded transition hover:bg-sky-200 disabled:opacity-50 disabled:cursor-not-allowed
                    disabled:hover:bg-white">
                View
            </button>
            <button v-if="canCancel"
                    :disabled="!canCancel"
                    @click="showCancelContactModal = true"
                    class="btn p-4 bg-white flex items-center justify-center h-10 text-red-500 font-bold border border-red-500 rounded transition hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed
                    disabled:hover:bg-white">
                Cancel
            </button>
        </div>
        <!-- Message -->
        <p class="text-sm" :class="{
            'text-black': canCancel,
            'text-sky-500': isOpen && !isUnread,
            'text-green-500': isClosed,
            'text-gray-500': isCanceled,
        }">
            {{ getMessage }}
        </p>

        <!-- View contact modal -->
        <Transition name="modal-fade">
            <ViewContactModal
                v-if="showViewContactModal"
                :contact="props.contact"
                @close="showViewContactModal = false"
            />
        </Transition>

        <!-- Cancel contact modal -->
        <Transition name="modal-fade">
            <CancelContactModal
                v-if="showCancelContactModal"
                :contact="props.contact"
                @close="showCancelContactModal = false"
                @confirm="cancelContact"
            />
        </Transition>

    </div>
</template>
