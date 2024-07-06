<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  system_users: {
    type: Array,
    required: true,
  },
  
  vehicle:{
    type: Object,
    required: true
  }
})

const form = useForm({
  name: props.vehicle.name,
  owner: props.vehicle.owner.id,
  main_user: props.vehicle.main_user.id,
  users: []
})

Array.from(props.vehicle.users).forEach(e => {
  form.users.push(e.id)
});

const submit = () => {
  form.patch(route('admin.vehicles.update', props.vehicle.id), {
    onSuccess: () => {
      router.visit(route('admin.vehicles.index'))
    },
  })
}

function back()
{
    window.history.back();
}

</script>

<template>
  <Head title="Edit Vehicle" />
  <AuthenticatedLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Edit Vehicle</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.name"
                label="Name"
                variant="underlined"
                type="text"
                :error-messages="form.errors.name"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">              
              <v-autocomplete
                v-model="form.users"
                label="Users"
                variant="underlined"
                :items="props.system_users"
                item-title="name"
                item-value="id"
                :error-messages="form.errors.users"
                chips
                multiple
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">              
              <v-autocomplete
                v-model="form.main_user"
                label="Main User"
                variant="underlined"
                :items="props.system_users"
                item-title="name"
                item-value="id"
                :error-messages="form.errors.main_user"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">              
              <v-autocomplete
                v-model="form.owner"
                label="Owner"
                variant="underlined"
                :items="props.system_users"
                item-title="name"
                item-value="id"
                :error-messages="form.errors.owner"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link href="#" @click="back" as="div">
            <v-btn text>Cancel</v-btn>
          </Link>
          <v-btn type="submit" color="primary">Save</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'VehiclesEdit',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'Vehicles',
          disabled: false,
          href: route('admin.vehicles.index'),
        },
        {
          title: 'Edit',
          disabled: true,
        },
      ],
    }
  },
}
</script>
