<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue';
import CancelReservationModal from './CancelReservationModal.vue'

const props = defineProps({
    reservation: Object,
})

const getMessage = computed(() => {
    const now = new Date();
    if (props.reservation.reservation_status === 'canceled') {
        return 'This reservation has been canceled.';
    }
    if (isCompleted.value && hasDeletedReview.value) {
        return 'You deleted your review for this reservation and cannot write a new one.';
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

const isCanceled = computed(() => {
    return props.reservation.reservation_status === 'canceled';
});

const isCompleted = computed(() => {
    const now = new Date();
    return props.reservation.reservation_status === 'booked' && new Date(props.reservation.end_at) < now;
});

const canCancel = computed(() => {
    if(props.reservation.reservation_status === 'canceled') {
        return false;
    }
    const now = new Date();
    const startAt = new Date(props.reservation.start_at);
    return props.reservation.reservation_status === 'booked' && startAt - now > 60 * 60 * 1000; // 1 hour in milliseconds
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
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
            <Link v-if="!hasDeletedReview"
                    :href="hasActiveReview ? route('reviews.edit', { reservation: props.reservation.id }) : route('reviews.create', { reservation: props.reservation.id })"
                    class="p-2 bg-sky-700 flex items-center justify-center h-10 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
                {{ hasActiveReview ? 'Edit Review': 'Leave Review' }}
            </Link>
            <button v-if="!isCanceled && !isCompleted"
                    :disabled="!canCancel"
                    @click="showCancelReservationModal = true"
                    class="btn p-4 bg-white flex items-center justify-center h-10 text-red-500 font-bold border border-red-500 rounded transition hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed
                    disabled:hover:bg-white">
                Cancel
            </button>
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
