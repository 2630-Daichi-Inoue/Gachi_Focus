<script setup>
import { Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import CancelReservationModal from "./CancelReservationModal.vue";

const props = defineProps({
    reservation: Object,
});

const getMessage = computed(() => {
    if (isPending.value) {
        return "Payment is pending. Your slot is held for 30 minutes — complete payment before it expires.";
    }
    if (props.reservation.reservation_status === "canceled") {
        return "This reservation has been canceled.";
    }
    if (isCompleted.value && hasDeletedReview.value) {
        return "You deleted your review for this reservation and cannot write a new one.";
    }
    if (isCompleted.value && hasActiveReview.value) {
        return "Thank you for your review.";
    }
    if (isCompleted.value) {
        return "We'd be glad if you could leave a review for this reservation. Thank you.";
    }
    if (canCancel.value) {
        return "You can cancel this reservation until 1 hour before it starts.";
    }
    if (!canCancel.value) {
        return "You cannot cancel because it's already less than 1 hour before it starts.";
    }
    return "";
});

const isPending = computed(() => {
    return props.reservation.reservation_status === "pending";
});

const isCanceled = computed(() => {
    return props.reservation.reservation_status === "canceled";
});

const isCompleted = computed(() => {
    const now = new Date();
    return (
        props.reservation.reservation_status === "booked" &&
        new Date(props.reservation.ended_at) < now
    );
});

const canCancel = computed(() => {
    if (props.reservation.reservation_status === "canceled") return false;
    if (isPending.value) return true;
    const now = new Date();
    const startedAt = new Date(props.reservation.started_at);
    return (
        props.reservation.reservation_status === "booked" &&
        startedAt - now > 60 * 60 * 1000
    );
});

const showCancelReservationModal = ref(false);
const cancelError = ref("");

const cancelReservation = () => {
    router.patch(
        route("reservations.cancel", props.reservation.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showCancelReservationModal.value = false;
                cancelError.value = "";
            },
            onError: () => {
                showCancelReservationModal.value = false;
                cancelError.value =
                    "Failed to cancel the reservation. Please try again.";
            },
        },
    );
};

const hasActiveReview = computed(() => {
    return !!props.reservation.review && !props.reservation.review.deleted_at;
});

const hasDeletedReview = computed(() => {
    return !!props.reservation.review && !!props.reservation.review.deleted_at;
});
</script>

<template>
    <div class="mx-2 flex w-full flex-col items-center gap-2">
        <!-- Button area -->
        <div class="flex w-full flex-col gap-2 md:flex-row md:justify-center">
            <Link
                v-if="isPending"
                :href="route('payments.checkout', reservation.id)"
                class="flex h-10 w-full items-center justify-center rounded border border-yellow-600 bg-yellow-500 p-2 font-bold text-white transition hover:bg-yellow-600 md:w-auto"
            >
                Pay Now
            </Link>
            <Link
                v-if="!hasDeletedReview && isCompleted"
                :href="
                    hasActiveReview
                        ? route('reviews.edit', { reservation: reservation.id })
                        : route('reviews.create', {
                              reservation: reservation.id,
                          })
                "
                class="flex h-10 w-full items-center justify-center rounded border border-gray-300 bg-sky-700 p-2 font-bold text-white transition hover:bg-sky-800 md:w-auto"
            >
                {{ hasActiveReview ? "Edit Review" : "Leave Review" }}
            </Link>
            <button
                v-if="!isCanceled && !isCompleted"
                :disabled="!canCancel"
                @click="showCancelReservationModal = true"
                class="flex h-10 w-full items-center justify-center rounded border border-red-500 bg-white p-2 font-bold text-red-500 transition hover:bg-red-200 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white md:w-auto"
            >
                Cancel
            </button>
            <Link
                :href="
                    route('contacts.create', { reservation_id: reservation.id })
                "
                class="flex h-10 w-full items-center justify-center rounded border border-gray-300 bg-slate-700 p-2 font-bold text-white transition hover:bg-sky-800 md:w-auto"
            >
                Contact Us
            </Link>
        </div>
        <!-- Cancel error -->
        <p v-if="cancelError" class="text-sm text-red-500">{{ cancelError }}</p>
        <!-- Message area -->
        <p
            class="text-sm"
            :class="{
                'text-gray-500':
                    isCanceled || (isCompleted && hasDeletedReview),
                'text-green-500': isCompleted && !hasDeletedReview,
                'text-black': canCancel,
                'text-red-500': !canCancel && !isCanceled && !isCompleted,
            }"
        >
            {{ getMessage }}
        </p>
        <!-- Cancel reservation modal area -->
        <Transition name="modal-fade">
            <CancelReservationModal
                v-if="showCancelReservationModal"
                :reservation="reservation"
                @close="showCancelReservationModal = false"
                @confirm="cancelReservation"
            />
        </Transition>
    </div>
</template>
