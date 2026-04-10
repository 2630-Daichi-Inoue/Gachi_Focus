<script setup>
import { reactive, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    space: Object,
    startCandidates: Array,
    date: String,
})

const page = usePage()

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

const isValidDateString = (value) => {
    return /^\d{4}-\d{2}-\d{2}$/.test(value)
}

watch(() => form.date, (newDate) => {
    form.startAt = null
    form.endAt = null

    if (!newDate) return
    if (!isValidDateString(newDate)) return

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
    router.get(route('reservations.payment', props.space.id), {
        date: form.date,
        start_at: form.startAt,
        end_at: form.endAt,
        quantity: form.quantity,
        total_price: totalPrice.value,
    })
}

const today = new Date(
    new Date().getTime() - new Date().getTimezoneOffset() * 60000
).toISOString().split('T')[0]

const startCandidatesExist = computed(() => {
    return props.startCandidates && props.startCandidates.length > 0
})

</script>

<template>
<div class="bg-white border border-gray-300 p-4">
        <form @submit.prevent="goToPayment" class="space-y-4">
            <div>
                <div class="flex flex-col mb-4 max-w-xs">
                    <label for="date" class="mb-1">Date</label>
                    <input
                        v-model="form.date"
                        name="date"
                        id="date"
                        type="date"
                        class="border rounded"
                        :min="today"
                    />
                </div>
                <p v-if="page.props.errors.date" class="text-red-600 text-sm">
                    {{ page.props.errors.date }}
                </p>

                <p class="mb-4">Price / 0.5 h:
                    <template v-if="form.date">
                        {{ formatPrice(pricePerHalfHour) }}
                    </template>
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
                    <div class="flex flex-col">
                        <label for="start_at" class="form-label">Start At</label>
                        <select v-model="form.startAt" name="start_at" id="start_at" class="border rounded">
                            <option v-for="candidate in startCandidates" :key="candidate" :value="candidate">{{ candidate }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="end_at" class="form-label">End At</label>
                        <select v-model="form.endAt" name="end_at" id="end_at" class="border rounded" :disabled="!form.startAt">
                            <option v-for="candidate in endCandidates" :key="candidate" :value="candidate">
                                {{ candidate }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input
                            v-model.number="form.quantity"
                            type="number"
                            id="quantity"
                            name="quantity"
                            class="border rounded"
                            :min="1"
                            :max="space.capacity"
                        />
                    </div>
                </div>

                <p v-if="page.props.errors.start_at" class="text-red-600 text-sm">
                    {{ page.props.errors.start_at }}
                </p>

                <p v-if="page.props.errors.end_at" class="text-red-600 text-sm">
                    {{ page.props.errors.end_at }}
                </p>

                <p v-if="page.props.errors.quantity" class="text-red-600 text-sm">
                    {{ page.props.errors.quantity }}
                </p>
            </div>

            <div>
                <p>Total Price:
                    {{ formatPrice(totalPrice) }}
                </p>
            </div>

            <div v-if="startCandidatesExist" class="flex flex-col md:flex-row gap-2">
                <Link :href="`/spaces/${space.id}`"
                        class="flex items-center justify-center md:w-1/4 text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                    Go Back
                </Link>
                <button type="submit"
                        class="flex ixtems-center justify-center md:w-3/4 text-white font-bold text-3xl border border-gray-500 rounded transition bg-cyan-600 hover:bg-cyan-700 p-2">
                    Continue to Payment
                </button>
            </div>
            <div v-else>
                <p class="text-red-600 text-sm">Sorry, but we have no available time slots for today.</p>
            </div>
        </form>
</div>
</template>
