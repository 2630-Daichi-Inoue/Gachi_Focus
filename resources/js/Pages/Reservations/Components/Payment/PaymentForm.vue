<script setup>
import { ref, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import { formatTime } from "@/utils/formatters";

const props = defineProps({
    space: Object,
    reservationData: Object,
    conflictingReservations: {
        type: Array,
        default: () => [],
    },
});

const overlapConfirmed = ref(false);
const hasConflicts = computed(() => props.conflictingReservations.length > 0);
const canSubmit = computed(
    () => (!hasConflicts.value || overlapConfirmed.value) && !form.processing,
);

const form = useForm({
    date: props.reservationData?.date,
    started_at: props.reservationData?.started_at,
    ended_at: props.reservationData?.ended_at,
    quantity: props.reservationData?.quantity,
});

const payment = () => {
    if (!props.reservationData) return;
    form.post(route("reservations.store", props.space.id));
};
</script>

<template>
    <div class="border border-gray-300 bg-white p-4">
        <form @submit.prevent="payment" class="space-y-4">
            <div>
                <h1 class="text-2xl text-gray-500">Cancellation Policy</h1>
                <p class="mb-2">
                    Cancellation can be done for free up to 1 hour before the
                    reservation start time.
                </p>

                <h1 class="text-2xl text-gray-500">Important Notes</h1>
                <p class="mb-2">
                    Your seat has not been reserved until the payment is
                    completed. It's probable that the reservation cannot be done
                    because of other users' actions.
                </p>
            </div>

            <div
                v-if="hasConflicts"
                class="space-y-3 rounded border border-yellow-400 bg-yellow-50 p-4"
            >
                <p class="font-semibold text-yellow-800">
                    You already have reservations that overlap with this time
                    slot:
                </p>
                <ul class="space-y-1">
                    <li
                        v-for="r in conflictingReservations"
                        :key="r.id"
                        class="text-sm text-yellow-800"
                    >
                        · {{ r.space.name }}: {{ formatTime(r.started_at) }} –
                        {{ formatTime(r.ended_at) }}
                    </li>
                </ul>
                <label
                    class="flex cursor-pointer select-none items-center gap-2"
                >
                    <input
                        type="checkbox"
                        v-model="overlapConfirmed"
                        class="h-4 w-4"
                    />
                    <span class="text-sm text-yellow-800"
                        >I understand and still want to proceed.</span
                    >
                </label>
            </div>

            <p v-if="form.errors.quantity" class="text-sm text-red-600">
                {{ form.errors.quantity }}
            </p>

            <div class="flex flex-col gap-2 md:flex-row">
                <Link
                    :href="
                        route('reservations.create', {
                            space: space.id,
                            date: reservationData.date,
                            started_at: reservationData.started_at,
                            ended_at: reservationData.ended_at,
                            quantity: reservationData.quantity,
                        })
                    "
                    class="flex items-center justify-center rounded border border-gray-500 p-2 text-3xl text-black transition hover:bg-gray-200 md:w-1/4"
                >
                    Go Back
                </Link>
                <button
                    type="submit"
                    :disabled="!canSubmit"
                    :class="
                        canSubmit
                            ? 'cursor-pointer bg-cyan-600 hover:bg-cyan-700'
                            : 'cursor-not-allowed bg-gray-400'
                    "
                    class="flex items-center justify-center rounded border border-gray-500 p-2 text-3xl font-bold text-white transition md:w-3/4"
                >
                    Pay with Stripe
                </button>
            </div>
        </form>
    </div>
</template>
