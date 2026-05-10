<script setup>
import InputError from "@/Components/InputError.vue";
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    reservation: Object,
});

const form = useForm({
    title: "",
    message: "",
    reservation_id: props.reservation?.id ?? null,
});

const submitContact = () => {
    form.post(route("contacts.store"));
};
</script>

<template>
    <div class="border border-gray-300 bg-white p-4">
        <form @submit.prevent="submitContact" class="space-y-4">
            <div class="space-y-4 p-4">
                <div class="w-full">
                    <label for="title" class="text-2xl text-gray-500"
                        >Title</label
                    >
                    <input
                        type="text"
                        name="title"
                        id="title"
                        v-model="form.title"
                        placeholder="Enter the title."
                        class="w-full rounded border border-gray-300 p-2"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-1" />
                </div>

                <div class="w-full">
                    <label for="message" class="text-2xl text-gray-500"
                        >Message</label
                    >
                    <textarea
                        name="message"
                        id="message"
                        v-model="form.message"
                        placeholder="Write your message here."
                        class="w-full rounded border border-gray-300 p-2"
                        rows="5"
                        required
                    >
                    </textarea>
                    <InputError :message="form.errors.message" class="mt-1" />
                </div>
            </div>

            <div class="flex flex-col gap-4 p-4 md:flex-row">
                <Link
                    v-if="props.reservation !== null"
                    :href="route('reservations.index')"
                    class="flex items-center justify-center rounded border border-gray-500 p-2 text-3xl text-black transition hover:bg-gray-200 md:w-1/2"
                >
                    Go Back
                </Link>
                <Link
                    v-if="props.reservation === null"
                    :href="route('spaces.index')"
                    class="flex items-center justify-center rounded border border-gray-500 p-2 text-3xl text-black transition hover:bg-gray-200 md:w-1/2"
                >
                    Go Back
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex items-center justify-center rounded border border-gray-500 bg-cyan-600 text-3xl font-bold text-white transition hover:bg-cyan-700 disabled:opacity-50 md:w-1/2"
                >
                    Submit Contact
                </button>
            </div>
        </form>
    </div>
</template>
