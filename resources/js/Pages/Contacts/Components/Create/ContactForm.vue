<script setup>
import InputError from '@/Components/InputError.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    reservation: Object,
})

const form = useForm({
    title: '',
    message: '',
    reservation_id: props.reservation?.id ?? null,
})

const submitContact = () => {
    form.post(route('contacts.store'))
}
</script>

<template>
<div class="bg-white border border-gray-300 p-4">
    <form @submit.prevent="submitContact" class="space-y-4">

        <div class="p-4 space-y-4">
            <div class="w-full">
                <label for="title" class="text-2xl text-gray-500">Title</label>
                <input type="text" name="title" id="title" v-model="form.title"
                    placeholder="Enter the title."
                    class="w-full border border-gray-300 rounded p-2"
                    required>
                <InputError :message="form.errors.title" class="mt-1" />
            </div>

            <div class="w-full">
                <label for="message" class="text-2xl text-gray-500">Message</label>
                <textarea
                        name="message"
                        id="message"
                        v-model="form.message"
                        placeholder="Write your message here."
                        class="w-full border border-gray-300 rounded p-2"
                        rows="5"
                        required>
                </textarea>
                <InputError :message="form.errors.message" class="mt-1" />
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
                    :disabled="form.processing"
                    class="flex items-center justify-center md:w-1/2 text-white font-bold text-3xl border border-gray-500 rounded transition bg-cyan-600 hover:bg-cyan-700 disabled:opacity-50">
                Submit Contact
            </button>
        </div>
    </form>
</div>
</template>
