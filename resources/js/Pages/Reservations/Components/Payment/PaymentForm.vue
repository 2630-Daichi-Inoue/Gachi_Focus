<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    space: Object,
    reservationData: Object,
})

const page = usePage()

const payment = () => {
    alert('The button is a dummy for now. Stripe checkout session will be implemented in the future development. The reservation gets confirmed only after the payment is completed.')
    if (!props.reservationData) return
    router.post(route('reservations.store', props.space.id), {
        date: props.reservationData.date,
        start_at: props.reservationData.start_at,
        end_at: props.reservationData.end_at,
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

        <p v-if="page.props.errors.quantity" class="text-red-600 text-sm">
            {{ page.props.errors.quantity }}
        </p>

        <div class="flex flex-col md:flex-row gap-2">
            <Link :href="route('reservations.create', {
                        space: space.id,
                        date: reservationData.date,
                        start_at: reservationData.start_at,
                        end_at: reservationData.end_at,
                        quantity: reservationData.quantity,
                    })"
                    class="flex items-center justify-center md:w-1/4 text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                Go Back
            </Link>
            <button type="submit"
                    class="flex items-center justify-center md:w-3/4 text-white font-bold text-3xl border border-gray-500 rounded transition bg-cyan-600 hover:bg-cyan-700">
                Pay with Stripe
            </button>
        </div>
    </form>
</div>
</template>
