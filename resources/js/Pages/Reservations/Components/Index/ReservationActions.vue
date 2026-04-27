<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue';
import CancelReservationModal from './CancelReservationModal.vue'

const props = defineProps({
    reservation: Object,
})

const getMessage = computed(() => {
    if (isPending.value) {
        return 'Payment is pending. Your slot is held for 30 minutes — complete payment before it expires.';
    }
    if (props.reservation.reservation_status === 'canceled') {
        return 'This reservation has been canceled.';
    }
    if (isCompleted.value && hasDeletedReview.value) {
        return 'You deleted your review for this reservation and cannot write a new one.';
    }
    if (isCompleted.value && hasActiveReview.value) {
        return 'Thank you for your review.';
    }
    if (isCompleted.value) {
        return 'We\'d be glad if you could leave a review for this reservation. Thank you.';
    }
    if (canCancel.value) {
        return 'You can cancel this reservation until 1 hour before it starts.';
    }
    if (!canCancel.value) {
        return "You cannot cancel because it's already less than 1 hour before it starts.";
    }
    return '';
});

const isPending = computed(() => {
    return props.reservation.reservation_status === 'pending';
});

const isCanceled = computed(() => {
    return props.reservation.reservation_status === 'canceled';
});

const isCompleted = computed(() => {
    const now = new Date();
    return props.reservation.reservation_status === 'booked' && new Date(props.reservation.ended_at) < now;
});

const canCancel = computed(() => {
    if (props.reservation.reservation_status === 'canceled') return false;
    if (isPending.value) return true;
    const now = new Date();
    const startedAt = new Date(props.reservation.started_at);
    return props.reservation.reservation_status === 'booked' && startedAt - now > 60 * 60 * 1000;
});

const showCancelReservationModal = ref(false);

const cancelReservation = () => {
    // Call the API to cancel the reservation
    router.patch(route('reservations.cancel', props.reservation.id), {},  {
        preserveScroll: true,
        onSuccess: () => {
            showCancelReservationModal.value = false;
        },
        onError: () => {
            alert('Failed to cancel the reservation. Please try again.');
        },
    })
};

const hasActiveReview = computed(() => {
    return !!props.reservation.review && !props.reservation.review.deleted_at
})

const hasDeletedReview = computed(() => {
    return !!props.reservation.review && !!props.reservation.review.deleted_at
})

</script>

<template>
    <div class="mx-2 flex flex-col gap-2 items-center w-full">
        <!-- Button area -->
        <div class="w-full flex flex-col md:flex-row md:justify-center gap-2">
            <Link v-if="isPending"
                    :href="route('payments.checkout', reservation.id)"
                    class="w-full md:w-auto p-2 bg-yellow-500 flex items-center justify-center h-10 text-white font-bold border border-yellow-600 rounded transition hover:bg-yellow-600">
                Pay Now
            </Link>
            <Link v-if="!hasDeletedReview && isCompleted"
                    :href="hasActiveReview ? route('reviews.edit', { reservation: reservation.id }) : route('reviews.create', { reservation: reservation.id })"
                    class="w-full md:w-auto p-2 bg-sky-700 flex items-center justify-center h-10 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
                {{ hasActiveReview ? 'Edit Review': 'Leave Review' }}
            </Link>
            <button v-if="!isCanceled && !isCompleted"
                    :disabled="!canCancel"
                    @click="showCancelReservationModal = true"
                    class="w-full md:w-auto p-2 bg-white flex items-center justify-center h-10 text-red-500 font-bold border border-red-500 rounded transition hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                Cancel
            </button>
            <Link :href="route('contacts.create', { reservation_id: reservation.id })"
                    class="w-full md:w-auto p-2 bg-slate-700 flex items-center justify-center h-10 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
                Contact Us
            </Link>
        </div>
        <!-- Message area -->
        <p class="text-sm" :class="{
            'text-gray-500': isCanceled || (isCompleted && hasDeletedReview),
            'text-green-500': isCompleted && !hasDeletedReview,
            'text-black': canCancel,
            'text-red-500': !canCancel && !isCanceled && !isCompleted,
        }">
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
