<script setup>
const props = defineProps({
    reservation: Object,
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(price);
}

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate();
    return `${month}/${day}/${year}`;
}

const formatTime = (dateStr) => {
    const getTimePart = (dateStr) => {
        const date = new Date(dateStr);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    };
    const [hour, minute] = getTimePart(dateStr).split(':');
    const date = new Date();
    date.setHours(hour);
    date.setMinutes(minute);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

const formatStatus = (status) => {
    const now = new Date();
    if(status === 'booked' && new Date(props.reservation.end_at) < now) {
        return 'Completed';
    }
    if(status === 'booked') {
        return 'Booked';
    }
    if(status === 'canceled') {
        return 'Canceled';
    }
    return '';
};

</script>

<template>
    <div class="flex flex-row items-center justify-around md:gap-4 w-full">
        <div class="flex flex-col gap-1">
            <p>{{ formatDate(reservation.start_at) }}</p>
            <p>{{ formatTime(reservation.start_at) }} - {{ formatTime(reservation.end_at) }}</p>
            <p>{{ formatPrice(reservation.total_price_yen) }}</p>
            <p>{{ reservation.quantity }} people</p>
        </div>
        <div class="md:flex md:justify-end">
            <div v-if="reservation.reservation_status === 'booked' && new Date(reservation.end_at) >= new Date()">
                 <p class="bg-sky-500 text-sky-900 text-xl px-2 py-1 rounded-full w-auto">
                    {{ formatStatus(reservation.reservation_status) }}
                 </p>
            </div>
            <div v-if="reservation.reservation_status === 'canceled'">
                <p class="bg-gray-500 text-white text-xl px-2 py-1 rounded-full w-auto">
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
            <div v-if="reservation.reservation_status === 'booked' && new Date(reservation.end_at) < new Date()">
                <p class="bg-green-500 text-green-900 text-xl px-2 py-1 rounded-full w-auto">
                    {{ formatStatus(reservation.reservation_status) }}
                </p>
            </div>
        </div>
    </div>
</template>
