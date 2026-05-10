<script setup>
import { router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import CancelContactModal from "./CancelContactModal.vue";
import ViewContactModal from "./ViewContactModal.vue";

const props = defineProps({
    contact: Object,
});

const isOpen = computed(() => props.contact.contact_status === "open");

const isUnread = computed(() => props.contact.read_at === null);

const isClosed = computed(() => props.contact.contact_status === "closed");

const isCanceled = computed(() => props.contact.contact_status === "canceled");

const canCancel = computed(() => isOpen.value && isUnread.value);

const showViewContactModal = ref(false);

const showCancelContactModal = ref(false);
const cancelError = ref("");

const cancelContact = () => {
    router.patch(
        route("contacts.cancel", props.contact.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showCancelContactModal.value = false;
                cancelError.value = "";
            },
            onError: () => {
                showCancelContactModal.value = false;
                cancelError.value =
                    "Failed to cancel the contact. Please try again.";
            },
        },
    );
};

const getMessage = computed(() => {
    if (isOpen.value && isUnread.value) {
        return "We have not checked this contact yet. You can cancel it if you want.";
    }
    if (isOpen.value && !isUnread.value) {
        return "We have checked this contact. Please wait for the response.";
    }
    if (isClosed.value) {
        return "This contact has been closed. Please contact us again if you have any questions.";
    }
    if (isCanceled.value) {
        return "This contact has been canceled by the user.";
    }
    return "";
});
</script>

<template>
    <div class="mx-2 flex w-full flex-col items-center gap-2">
        <!-- Buttons -->
        <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
            <button
                @click="showViewContactModal = true"
                class="btn flex h-10 items-center justify-center rounded border border-sky-500 bg-white p-4 font-bold text-sky-500 transition hover:bg-sky-200 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white"
            >
                View
            </button>
            <button
                v-if="canCancel"
                :disabled="!canCancel"
                @click="showCancelContactModal = true"
                class="btn flex h-10 items-center justify-center rounded border border-red-500 bg-white p-4 font-bold text-red-500 transition hover:bg-red-200 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white"
            >
                Cancel
            </button>
        </div>
        <p v-if="cancelError" class="text-sm text-red-500">{{ cancelError }}</p>
        <!-- Message -->
        <p
            class="text-sm"
            :class="{
                'text-black': canCancel,
                'text-sky-500': isOpen && !isUnread,
                'text-green-500': isClosed,
                'text-gray-500': isCanceled,
            }"
        >
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
