<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TripCard from '@/Components/TripCard.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { computed, ref } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'

const breadcrumbs = ref([
    {
        title: 'Trips',
        disabled: true,
    },
])

const page = usePage()

const vehicles = computed(() => page.props.vehicles)
const categories = computed(() => page.props.categories)
const trips = computed(() => page.props.trips.data)

function infiniteLoad() {
    router.get(page.props.trips.next_page_url)
}
</script>

<template>

    <Head title="Trip" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">Trip</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-row>
            <v-col v-for="trip in trips">
                <TripCard :categories="categories" :vehicles="vehicles" :trip="trip"></TripCard>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
