<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  roles: {
    type: Array,
    required: true,
  }
})

const form = useForm({
  email: null,
  role: null
})

const submit = () => {
  form.post(route('admin.users.invite.store'), {
    onSuccess: () => {
      router.visit(route('admin.users'))
    },
  })
}

const allowedRoles = props.roles.slice(0, props.roles.indexOf(usePage().props.auth.user.roles[0].name)+1);
</script>

<template>
  <Head title="Create User" />
  <AuthenticatedLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Create User</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.email"
                label="Email"
                variant="underlined"
                type="email"
                :error-messages="form.errors.email"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-select
                v-model="form.role"
                label="Role"
                variant="underlined"
                :items="allowedRoles"
                :error-messages="form.errors.role"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link :href="route('admin.users.index')" as="div">
            <v-btn text>Cancel</v-btn>
          </Link>
          <v-btn type="submit" color="primary" append-icon="mdi-send">Send Invitation</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'UsersInvite',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'Users',
          disabled: false,
          href: route('admin.users.index'),
        },
        {
          title: 'Invite',
          disabled: true,
        },
      ],
    }
  },
}
</script>
