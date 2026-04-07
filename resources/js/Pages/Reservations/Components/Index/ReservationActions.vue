<script setup>
import { Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue';
import CancelReservationModal from './CancelReservationModal.vue'

const props = defineProps({
    reservation: Object,
})

const canCancel = computed(() => {
    if(props.reservation.reservation_status === 'canceled') {
        return false;
    }
    const now = new Date();
    const startAt = new Date(props.reservation.start_at);
    return props.reservation.reservation_status === 'booked' && startAt - now > 60 * 60 * 1000; // 1 hour in milliseconds
});

const getMessage = computed(() => {
    const now = new Date();
    if (props.reservation.reservation_status === 'canceled') {
        return 'This reservation has been canceled.';
    }
    if (props.reservation.reservation_status === 'booked' && isCompleted.value) {
        return 'This reservation has been completed.';
    }
    if (props.reservation.reservation_status === 'booked' && canCancel.value) {
        return 'You can cancel this reservation until 1 hour before it starts.';
    }
    if (props.reservation.reservation_status === 'booked' && !canCancel.value) {
        return "You cannot cancel because it's already less than 1 hour before it starts.";
    }
    return '';
});

const isCompleted = computed(() => {
    const now = new Date();
    return props.reservation.reservation_status === 'booked' && new Date(props.reservation.end_at) < now;
});

const isCanceled = computed(() => {
    return props.reservation.reservation_status === 'canceled';
});

const showCancelReservationModal = ref(false);

const cancelReservation = () => {
    showCancelReservationModal.value = false;
    // Emit an event to parent component to trigger cancellation
    // You can also directly call an API here to cancel the reservation
    // For example:
    // axios.post(`/api/reservations/${props.reservation.id}/cancel`).then(() => {
    //     // Handle successful cancellation (e.g., refresh data, show notification)
    // }).catch(() => {
    //     // Handle error (e.g., show error notification)
    // });
    // For now, we'll just log to console
};
</script>

<template>
    <div class="mx-2 flex flex-col gap-2 items-center w-full">
        <!-- Button area -->
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
            <Link v-if="isCompleted"
                    :href="`/reservations/${reservation.id}/review`"
                    class="p-2 bg-sky-700 flex items-center justify-center h-10 text-white font-bold border border-gray-300 rounded transition hover:bg-sky-800">
                Leave Review
            </Link>
            <button :disabled="!canCancel"
                    @click="showCancelReservationModal = true"
                    class="btn p-4 bg-white flex items-center justify-center h-10 text-red-500 font-bold border border-red-500 rounded transition hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed
                    disabled:hover:bg-white">
                Cancel
            </button>
        </div>
        <!-- Message area -->
        <p class="text-sm" :class="{
            'text-gray-500': isCanceled,
            'text-green-500': isCompleted,
            'text-black': canCancel,
            'text-red-500': !canCancel,
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
