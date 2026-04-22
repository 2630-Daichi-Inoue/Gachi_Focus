<script setup>

import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
    reservation: Object,
})

const form = reactive({
    title: '',
    message:  '',
})

const submitContact = () => {
    if (props.reservation !== null) {
        router.post(route('contacts.store'), {
            title: form.title,
            message: form.message,
            reservation_id: props.reservation.id
        })
    } else {
        router.post(route('contacts.store'), {
            title: form.title,
            message: form.message,
            reservation_id: null
        })
    }
}

</script>

<template>
<div class="bg-white border border-gray-300 p-4">
    <form @submit.prevent="submitContact" class="space-y-4">

        <div class="p-4 space-y-4">
            <!-- <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/2">
                    <img :src="reservation.space.image_path ? `/storage/${reservation.space.image_path}` : '/images/no-image.png'"
                        :alt="`space ${reservation.space.name}`"
                        class="object-cover rounded border border-gray-300"
                    >
                </div>

                <div class="w-full md:w-1/2">
                    <h1 class="text-3xl font-bold mb-2">{{ reservation.space.name }}</h1>
                </div>
            </div> -->

            <div class="w-full">
                <label for="title" class="text-2xl text-gray-500">Title</label>
                <input type="text" name="title" id="title" v-model="form.title" placeholder="Enter the title." class="w-full border border-gray-300 rounded p-2">
            </div>

            <div class="w-full">
                <label for="message" class="text-2xl text-gray-500">Message</label>
                <textarea
                        name="message"
                        id="message"
                        v-model="form.message"
                        placeholder="Write your message here."
                        class="w-full border border-gray-300 rounded p-2"
                        rows="5">
                </textarea>
            </div>
        </div>

        <div class="p-4 flex flex-col md:flex-row gap-4">
            <Link v-if="props.reservation !== null"
                    :href="route('reservations.index')"
                    class="flex items-center justify-center md:w-1/2 text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                Go Back
            </Link>
            <Link v-if="props.reservation === null"
                    :href="route('spaces.index')"
                    class="flex items-center justify-center md:w-1/2 text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                Go Back
            </Link>
            <button type="submit"
                    class="flex items-center justify-center md:w-1/2 text-white font-bold text-3xl border border-gray-500 rounded transition bg-cyan-600 hover:bg-cyan-700">
                Submit Contact
            </button>
        </div>
    </form>
</div>
</template>
