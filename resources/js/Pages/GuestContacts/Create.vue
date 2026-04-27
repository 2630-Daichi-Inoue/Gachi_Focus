<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    title: '',
    message: '',
});

const submit = () => {
    form.post(route('guest-contact.store'));
};
</script>

<template>
    <Head title="Contact Us" />

    <div class="min-h-screen flex items-center justify-center" style="background-color: #f8f8f8;">
        <div class="bg-white rounded-lg w-full shadow-md" style="max-width: 520px; padding: 48px 40px;">

            <div class="flex justify-center mb-2">
                <img src="/images/GachiFocus_logo.png" alt="GachiFocus" class="h-24">
            </div>

            <p class="text-center text-sm text-gray-400 mb-8">Contact us and we will get back to you as soon as possible.</p>

            <form @submit.prevent="submit" class="space-y-5">

                <div>
                    <label for="email" class="block mb-1 text-sm">Email</label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="w-full px-3 py-2 text-sm rounded outline-none"
                        style="border: 1px solid #dcdcdc;"
                        placeholder="Enter your email address"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

                <div>
                    <label for="title" class="block mb-1 text-sm">Title</label>
                    <input
                        id="title"
                        type="text"
                        v-model="form.title"
                        class="w-full px-3 py-2 text-sm rounded outline-none"
                        style="border: 1px solid #dcdcdc;"
                        placeholder="Enter the title"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-1" />
                </div>

                <div>
                    <label for="message" class="block mb-1 text-sm">Message</label>
                    <textarea
                        id="message"
                        v-model="form.message"
                        class="w-full px-3 py-2 text-sm rounded outline-none"
                        style="border: 1px solid #dcdcdc;"
                        placeholder="Write your message here"
                        rows="5"
                        required
                    ></textarea>
                    <InputError :message="form.errors.message" class="mt-1" />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-2 rounded text-white text-sm transition-colors duration-200"
                        style="background-color: #222;"
                        :disabled="form.processing"
                        @mouseover="e => e.currentTarget.style.backgroundColor = '#444'"
                        @mouseleave="e => e.currentTarget.style.backgroundColor = '#222'"
                    >
                        Submit
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-800">
                    Back to Login
                </Link>
            </div>
        </div>
    </div>
</template>
