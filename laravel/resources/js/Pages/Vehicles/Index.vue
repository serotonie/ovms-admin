<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import VehicleCard from '@/Components/VehicleCard.vue'
import { computed, ref } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'

const breadcrumbs = ref([
    {
        title: 'Vehicles',
        disabled: true,
    },
])

const page = usePage()

const vehicles = computed(() => page.props.vehicles.data)
</script>

<template>

    <Head title="Vehicle" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">Vehicle</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-row>
            <v-col v-for="vehicle in vehicles">
                <VehicleCard :vehicle="vehicle"></VehicleCard>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
