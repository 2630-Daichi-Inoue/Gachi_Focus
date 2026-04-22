<script setup>

import { computed } from 'vue';

const props = defineProps({
    contact: Object,
})

const isOpen = computed(() => {
    return props.contact.contact_status === 'open';
});

const isClosed = computed(() => {
    return props.contact.contact_status === 'closed';
});

const isCanceled = computed(() => {
    return props.contact.contact_status === 'canceled';
});

const formatStatus = (status) => {
    if(status === 'canceled') {
        return 'Completed';
    }
    if(status === 'open') {
        return 'Open';
    }
    if(status === 'closed') {
        return 'Closed';
    }
    return '';
};

const statusInfo = computed(() => {
    if(props.contact.contact_status === 'open') {
        return {
            label: 'Open',
            class: 'bg-sky-500 text-sky-900',
        };
    }
    if(props.contact.contact_status === 'closed') {
        return {
            label: 'Closed',
            class: 'bg-green-500 text-green-900',
        };
    }
    if(props.contact.contact_status === 'canceled') {
        return {
            label: 'Canceled',
            class: 'bg-gray-500 text-white',
        };
    }
    return {
        label: '',
        class: '',
    };
});

</script>

<template>
    <div class="flex flex-row items-center justify-around md:gap-4 w-full">
        <div class="md:flex md:justify-end">
            <div v-if="statusInfo">
                <p :class="[statusInfo.class, 'text-xl', 'px-2', 'py-1', 'rounded-full', 'w-auto']">
                    {{ statusInfo.label }}
                </p>
            </div>
        </div>
    </div>
</template>
