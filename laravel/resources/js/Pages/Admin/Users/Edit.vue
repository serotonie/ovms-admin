<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  roles: {
    type: Array,
    required: true,
  },
  auth: {
    type: Object,
    required: true,
  }
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role: props.user.role
})

const submit = () => {
  form.patch(route('admin.users.update', props.user.id), {
    onSuccess: () => {
      router.visit(route('admin.users.index'))
    },
  })
}

const allowedRoles = props.roles.slice(0, props.roles.indexOf(props.auth.user.roles[0].name) + 1);

function back() {
  window.history.back();
}

</script>

<template>

  <Head title="Edit User" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Edit User</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.name" label="Name" variant="underlined" :error-messages="form.errors.name" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.email" label="Email" variant="underlined" type="email"
                :error-messages="form.errors.email" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-select v-model="form.role" label="Role" variant="underlined" :items="allowedRoles"
                :error-messages="form.errors.role" />
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
  name: 'UsersEdit',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'Users',
          disabled: false,
          href: route('admin.users.index'),
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
