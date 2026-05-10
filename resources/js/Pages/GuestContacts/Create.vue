<script setup>
import InputError from "@/Components/InputError.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const form = useForm({
    email: "",
    title: "",
    message: "",
});

const flashError = computed(() => usePage().props.flash?.error);

const submit = () => {
    form.post(route("guest-contact.store"));
};
</script>

<template>
    <Head title="Contact Us" />

    <div
        class="flex min-h-screen items-center justify-center"
        style="background-color: #f8f8f8"
    >
        <div
            class="w-full rounded-lg bg-white shadow-md"
            style="max-width: 520px; padding: 48px 40px"
        >
            <div class="mb-2 flex justify-center">
                <img
                    src="/images/GachiFocus_logo.png"
                    alt="GachiFocus"
                    class="h-24"
                />
            </div>

            <p class="mb-8 text-center text-sm text-gray-400">
                Contact us and we will get back to you as soon as possible.
            </p>

            <div
                v-if="flashError"
                class="mb-4 rounded bg-red-100 px-4 py-3 text-sm text-red-700"
            >
                {{ flashError }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="mb-1 block text-sm">Email</label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="w-full rounded px-3 py-2 text-sm outline-none"
                        style="border: 1px solid #dcdcdc"
                        placeholder="Enter your email address"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

                <div>
                    <label for="title" class="mb-1 block text-sm">Title</label>
                    <input
                        id="title"
                        type="text"
                        v-model="form.title"
                        class="w-full rounded px-3 py-2 text-sm outline-none"
                        style="border: 1px solid #dcdcdc"
                        placeholder="Enter the title"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-1" />
                </div>

                <div>
                    <label for="message" class="mb-1 block text-sm"
                        >Message</label
                    >
                    <textarea
                        id="message"
                        v-model="form.message"
                        class="w-full rounded px-3 py-2 text-sm outline-none"
                        style="border: 1px solid #dcdcdc"
                        placeholder="Write your message here"
                        rows="5"
                        required
                    ></textarea>
                    <InputError :message="form.errors.message" class="mt-1" />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full rounded py-2 text-sm text-white transition-colors duration-200"
                        style="background-color: #222"
                        :disabled="form.processing"
                        @mouseover="
                            (e) =>
                                (e.currentTarget.style.backgroundColor = '#444')
                        "
                        @mouseleave="
                            (e) =>
                                (e.currentTarget.style.backgroundColor = '#222')
                        "
                    >
                        Submit
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <Link
                    :href="route('login')"
                    class="text-sm text-gray-500 hover:text-gray-800"
                >
                    Back to Login
                </Link>
            </div>
        </div>
    </div>
</template>
