<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Vehicle from '../Components/Vehicles/Vehicles.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="My Vehicle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Vehicles</h2>
        </template>
        <Vehicle></Vehicle>
    </AuthenticatedLayout>
</template>
