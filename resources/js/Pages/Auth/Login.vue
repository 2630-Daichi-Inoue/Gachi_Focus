<script setup>
import InputError from "@/Components/InputError.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: Boolean,
    status: String,
    banned: Boolean,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center"
        style="background-color: #f8f8f8"
    >
        <Head title="Login" />

        <div
            class="w-full rounded-lg bg-white shadow-md"
            style="max-width: 420px; padding: 48px 40px"
        >
            <div class="mb-2 flex justify-center">
                <img
                    src="/images/GachiFocus_logo.png"
                    alt="GachiFocus"
                    class="h-24"
                />
            </div>

            <p class="mb-8 text-center text-sm text-gray-400">
                Find your ideal workspace.
            </p>

            <div v-if="status" class="mb-4 text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div class="mb-5">
                    <label for="email" class="mb-1 block text-sm">Email</label>
                    <div
                        class="flex overflow-hidden rounded"
                        style="border: 1px solid #dcdcdc"
                    >
                        <span
                            class="flex items-center bg-gray-50 px-3 text-gray-400"
                            style="border-right: 1px solid #dcdcdc"
                        >
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="flex-1 px-3 py-2 text-sm outline-none"
                            placeholder="Enter Your Email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

                <div class="mb-5">
                    <label for="password" class="mb-1 block text-sm"
                        >Password</label
                    >
                    <div
                        class="flex overflow-hidden rounded"
                        style="border: 1px solid #dcdcdc"
                    >
                        <span
                            class="flex items-center bg-gray-50 px-3 text-gray-400"
                            style="border-right: 1px solid #dcdcdc"
                        >
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="flex-1 px-3 py-2 text-sm outline-none"
                            placeholder="Enter Password"
                            required
                            autocomplete="current-password"
                        />
                    </div>
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div class="mb-3 mt-8">
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
                        Login
                    </button>
                </div>

                <div class="text-center">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Forgot your password?
                    </Link>
                </div>
            </form>

            <div class="mt-5">
                <Link
                    :href="route('register')"
                    class="block w-full rounded py-2 text-center text-sm transition-colors duration-200"
                    style="border: 1px solid #374151"
                    @mouseover="
                        (e) =>
                            (e.currentTarget.style.backgroundColor = '#f3f4f6')
                    "
                    @mouseleave="
                        (e) =>
                            (e.currentTarget.style.backgroundColor =
                                'transparent')
                    "
                >
                    Register now!
                </Link>
            </div>

            <div v-if="banned" class="mt-4 text-center">
                <p class="text-sm text-gray-500">
                    Need help?
                    <Link
                        :href="route('guest-contact.create')"
                        class="text-sm text-red-600 underline hover:text-red-800"
                    >
                        Contact us
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
