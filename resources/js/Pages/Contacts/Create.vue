<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ContactForm from './Components/Create/ContactForm.vue'

const props = defineProps({
    reservation: Object,
})

</script>

<template>
    <AuthenticatedLayout>
        <Head :title="'Make a contact'" />
        <div class="w-full">
            <ContactForm :reservation="reservation" />
        </div>
    </AuthenticatedLayout>
</template>
