<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import VehicleCard from '@/Components/VehicleCard.vue';
import { Head, Link } from '@inertiajs/vue3'
import usePermissions from '@/../../vendor/wijzijnweb/laravel-inertia-permissions/resources/js/Uses/usePermissions.ts';
const { can } = usePermissions()
</script>

<template>

    <Head title="Vehicle" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">Vehicle</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <VehicleCard></VehicleCard>
    </AuthenticatedLayout>
</template>

<script>
export default {
    name: 'VehiclesIndex',
    props: {
        data: {
            type: Object,
        },
    },
    data() {
        return {
            breadcrumbs: [
                {
                    title: 'Vehicles',
                    disabled: true,
                },
            ]
        }
    }
}
</script>
