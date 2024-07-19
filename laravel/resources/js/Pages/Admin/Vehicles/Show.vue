<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'
import usePermissions from '@/../../vendor/wijzijnweb/laravel-inertia-permissions/resources/js/Uses/usePermissions.ts';
const { can } = usePermissions()

const props = defineProps({
  vehicle: {
    type: Object,
    required: true,
  }
})
</script>

<template>

  <Head :title="props.vehicle.name" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">{{ props.vehicle.name }}</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card class="pa-4">
      {{ props.vehicle }}
    </v-card>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'VehiclesShow',
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
          disabled: false,
          href: route('vehicles.index')
        },
        {
          title: 'Show',
          disabled: true,
        },
      ]
    }
  },
  methods: {

  }
}
</script>
