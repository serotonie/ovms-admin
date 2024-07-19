<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TripCard from '@/Components/TripCard.vue'
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    vehicles: Object,
    categories: Object,
    trips: Object
})

const vehicles = props.vehicles
const categories = props.categories
const trips = ref(props.trips.data)
const nextCursor = ref(props.trips.next_page_url)

async function infiniteLoad({ done }) {
    try {
        const result = await axios.get(nextCursor.value)
        trips.value.push(...result.data.data)
        if (result.data.next_page_url === null) {
            done('empty')
        }
        else {
            nextCursor.value = result.data.next_page_url
            done('ok')
        }
    } catch (error) {
        done('error')
    }
}
</script>

<template>

    <Head title="My Trips" />
    <AuthenticatedLayout title="My Trips">

        <v-infinite-scroll color="secondary" @load="infiniteLoad" class="px-3">
            <v-row>
                <v-col v-for="trip in trips">
                    <TripCard :categories="categories" :vehicles="vehicles" :trip="trip"></TripCard>
                </v-col>
            </v-row>
            <template v-slot:empty>
                <v-alert type="info">No more trips!</v-alert>
            </template>
            <template v-slot:error="{ props }">
                <v-alert type="error">
                    <div class="d-flex justify-space-between align-center">
                        Something went wrong...
                        <v-btn color="white" size="small" variant="outlined" v-bind="props">
                            Retry
                        </v-btn>
                    </div>
                </v-alert>
            </template>
        </v-infinite-scroll>
    </AuthenticatedLayout>
</template>
