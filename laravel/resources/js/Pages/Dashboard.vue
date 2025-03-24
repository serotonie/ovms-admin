<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  vehicles: {
    type: Number,
    required: true
  }
})
</script>

<template>

  <Head title="Dashboard" />
  <AuthenticatedLayout title="Dashboard">
    <v-card v-if="vehicles === 0">
      <v-card-text>
        <v-empty-state icon="mdi-car-search"
          text="Welcome to the OVMS Admin Dashboard, add your first vehicle to begin using it"
          title="We didn't find any vehicle yet." action-text="add first vehicle"
          @click:action="router.visit(route('admin.vehicles.create'))"></v-empty-state>
        <v-empty-state icon="mdi-car-search"
          text="Welcome to the OVMS Admin Dashboard, ask your admin to add a vehicle for you"
          title="We didn't find any vehicle yet."></v-empty-state>
      </v-card-text>
    </v-card>
    <v-card v-else>
      Hello everyone
    </v-card>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'DashboardPage',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'Dashboard',
          disabled: true,
        },
      ],
    }
  },
}
</script>
