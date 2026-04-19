<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { formatTime } from '@/utils/formatters'

const props = defineProps({
    space: Object,
    reservationData: Object,
    conflictingReservations: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()
const overlapConfirmed = ref(false)
const hasConflicts = computed(() => props.conflictingReservations.length > 0)
const canSubmit = computed(() => !hasConflicts.value || overlapConfirmed.value)

const payment = () => {
    alert('The button is a dummy for now. Stripe checkout session will be implemented in the future development. The reservation gets confirmed only after the payment is completed.')
    if (!props.reservationData) return
    router.post(route('reservations.store', props.space.id), {
        date: props.reservationData.date,
        started_at: props.reservationData.started_at,
        ended_at: props.reservationData.ended_at,
        quantity: props.reservationData.quantity,
    })
}
</script>

<template>
<div class="bg-white border border-gray-300 p-4">
    <form @submit.prevent="payment" class="space-y-4">
        <div>
            <div class="bg-lime-100 border border-amber-600 p-2">
                <h1 class="text-2xl text-gray-500">Memo</h1>
                <p class="mb-2">Input boxes from Stripe's payment elements will go here in the future development.</p>
            </div>

            <h1 class="text-2xl text-gray-500">Cancellation Policy</h1>
            <p class="mb-2">Cancellation can be done for free up to 1 hour before the reservation start time.</p>

            <h1 class="text-2xl text-gray-500">Important Notes</h1>
            <p class="mb-2">Your seat has not been reserved until the payment is completed. It's probable that the reservation cannot be done because of other users' actions.</p>
        </div>

        <div v-if="hasConflicts" class="bg-yellow-50 border border-yellow-400 rounded p-4 space-y-3">
            <p class="font-semibold text-yellow-800">You already have reservations that overlap with this time slot:</p>
            <ul class="space-y-1">
                <li v-for="r in conflictingReservations" :key="r.id" class="text-yellow-800 text-sm">
                    · {{ r.space.name }}: {{ formatTime(r.started_at) }} – {{ formatTime(r.ended_at) }}
                </li>
            </ul>
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" v-model="overlapConfirmed" class="w-4 h-4" />
                <span class="text-yellow-800 text-sm">I understand and still want to proceed.</span>
            </label>
        </div>

        <p v-if="page.props.errors.quantity" class="text-red-600 text-sm">
            {{ page.props.errors.quantity }}
        </p>

        <div class="flex flex-col md:flex-row gap-2">
            <Link :href="route('reservations.create', {
                        space: space.id,
                        date: reservationData.date,
                        started_at: reservationData.started_at,
                        ended_at: reservationData.ended_at,
                        quantity: reservationData.quantity,
                    })"
                    class="flex items-center justify-center md:w-1/4 text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                Go Back
            </Link>
            <button type="submit"
                    :disabled="!canSubmit"
                    :class="canSubmit ? 'bg-cyan-600 hover:bg-cyan-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed'"
                    class="flex items-center justify-center md:w-3/4 text-white font-bold text-3xl border border-gray-500 rounded transition p-2">
                Pay with Stripe
            </button>
        </div>
    </form>
</div>
</template>
