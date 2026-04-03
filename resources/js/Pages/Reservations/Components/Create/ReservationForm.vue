<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive, computed, watch } from 'vue'

const props = defineProps({
    space: Object,
    startCandidates: Array,
    date: String,
})

const form = reactive({
    date: props.date ?? '',
    startAt: null,
    endAt: null,
    quantity: 1,
})

const toMinutes = (time) => {
    const [hour, minute] = time.split(':').map(Number)
    return hour * 60 + minute
}

const endCandidates = computed(() => {
    if (!form.startAt) {
        return []
    }

    const startMinutes = toMinutes(form.startAt)

    const allPossibleEndCandidates = [
        ...props.startCandidates.filter(candidate => candidate > form.startAt),
        formatTime(props.space.close_time),
    ]

    return allPossibleEndCandidates.filter(candidate => {
        const endMinutes = toMinutes(candidate)
        return endMinutes - startMinutes <= 480
    })
})

const formatTime = (time) => {
    if (!time) return ''
    return time.split(':').slice(0, 2).join(':')
}

watch(() => form.startAt, () => {
    form.endAt = null
})

const emit = defineEmits(['update:date'])

watch(() => form.date, (newDate) => {
    form.startAt = null
    form.endAt = null
    emit('update:date', newDate)
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(price);
};

const getDayOfWeek = (dateString) => {
    const [year, month, day] = dateString.split('-').map(Number)

    return new Date(year, month - 1, day).getDay()
}

const isWeekend = computed(() => {
    if (!form.date) return false
    const day = getDayOfWeek(form.date)
    return day === 0 || day === 6
})

const slotCount = computed(() => {
    if (!form.startAt || !form.endAt) return 0
    return (toMinutes(form.endAt) - toMinutes(form.startAt)) / 30
})

const pricePerHalfHour = computed(() => {
    return isWeekend.value
        ? props.space.weekend_price_yen
        : props.space.weekday_price_yen
})

const totalPrice = computed(() => {
    if (!form.date || !form.startAt || !form.endAt || !form.quantity) {
        return 0
    }

    return pricePerHalfHour.value * form.quantity * slotCount.value
})

const goToPayment = () => {
    if (!form.date || !form.startAt || !form.endAt || !form.quantity) {
        alert('Please fill in all fields.')
        return
    }
    // alert('Going to payment page.')
    router.get(route('reservations.payment', props.space.id), {
        date: form.date,
        start_at: form.startAt,
        end_at: form.endAt,
        quantity: form.quantity,
        total_price: totalPrice.value,
    })
}

</script>

<template>
<div class="bg-white border border-gray-300 p-4">
    <form @submit.prevent="goToPayment" class="space-y-4">
        <div>
            <label for="date">Date</label>
            <input v-model="form.date" name="date" id="date" type="date" placeholder="Date" class="border rounded mb-4" />
            <p class="mb-4">Price / 0.5 h:
                <template v-if="form.date">
                    {{ formatPrice(pricePerHalfHour) }}
                </template>
            </p>
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <label for="start_at" class="form-label">Start At</label>
                <select v-model="form.startAt" name="start_at" id="start_at" class="border rounded">
                    <option v-for="candidate in startCandidates" :key="candidate" :value="candidate">{{ candidate }}</option>
                </select>
                <label for="end_at" class="form-label">End At</label>
                <select v-model="form.endAt" name="end_at" id="end_at" class="border rounded" :disabled="!form.startAt">
                    <option v-for="candidate in endCandidates" :key="candidate" :value="candidate">
                        {{ candidate }}
                    </option>
                </select>
                <label for="quantity" class="form-label">Quantity</label>
                <input v-model.number="form.quantity" type="number" id="quantity" name="quantity" class="border rounded" :min="1" :max="space.capacity"/>
            </div>
        </div>

        <div>
            <p>Total Price:
                {{ formatPrice(totalPrice) }}
            </p>
        </div>

        <div class="flex flex-col md:flex-row gap-2">
            <Link :href="`/spaces/${space.id}`"
                    class="flex items-center justify-center md:w-1/4 text-black text-3xl border border-gray-300 rounded transition hover:bg-gray-200 p-2">
                Go Back
            </Link>
            <button type="submit"
                    class="flex items-center justify-center md:w-3/4 text-white font-bold text-3xl border border-gray-300 rounded transition bg-cyan-600 hover:bg-cyan-700">
                Continue to Payment
            </button>
        </div>
    </form>
</div>
</template>
