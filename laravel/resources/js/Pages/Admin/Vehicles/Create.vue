<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import ImagePicker from '@/Components/ImagePicker.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { nanoid } from 'nanoid'
import { ref } from 'vue'

const props = defineProps({
  system_users: {
    type: Array,
    required: false
  },
  auth: {
    type: Object,
    required: true
  },
  mqtt: {
    type: Object,
    required: false
  }
})

const form = useForm({
  name: null,
  owner: null,
  main_user: null,
  users: null,
  module_id: nanoid(12),
  module_username: nanoid(12),
  module_pwd: nanoid(),
  picture: null
})

const creation_success_dialog = ref(false);

const submit = () => {
  form.post(route('admin.vehicles.store'), {
    preserveState: true,
    onSuccess: () => {
      creation_success_dialog.value = true;
    }
  })
}

</script>

<template>

  <Head title="Create Vehicle" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Create Vehicle</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.name" label="Name" variant="underlined" type="text"
                :error-messages="form.errors.name" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-autocomplete v-model="form.users" label="Users" variant="underlined" :items="props.system_users"
                item-title="name" item-value="id" :error-messages="form.errors.users" chips multiple />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-autocomplete v-model="form.main_user" label="Main User" variant="underlined"
                :items="props.system_users" item-title="name" item-value="id" :error-messages="form.errors.main_user" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-autocomplete v-model="form.owner" label="Owner" variant="underlined" :items="props.system_users"
                item-title="name" item-value="id" :error-messages="form.errors.owner" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.module_id" append-inner-icon="mdi-reload"
                @click:append-inner="form.module_id = nanoid(12)" label="Module ID" variant="underlined"
                :error-messages="form.errors.module_id" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.module_username" append-inner-icon="mdi-reload"
                @click:append-inner="form.module_username = nanoid(12)" label="Module Username" variant="underlined"
                :error-messages="form.errors.module_username" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.module_pwd" append-inner-icon="mdi-reload"
                @click:append-inner="form.module_pwd = nanoid()" label="Module Password" variant="underlined"
                :error-messages="form.errors.module_pwd" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <ImagePicker v-model="form.picture" defaultImage="/car_default.png"></ImagePicker>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link :href="route('admin.vehicles.index')" as="div">
          <v-btn text>Cancel</v-btn>
          </Link>
          <v-btn type="submit" color="primary" append-icon="mdi-content-save" :loading="form.processing">Save</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>

    <v-dialog v-model="creation_success_dialog" persistent width="600">
      <v-alert type="success" :value="true">
        Congrats, the vehicle has been succefully added
      </v-alert>
      <v-card>
        <v-card-title>
          Now, configure the module
        </v-card-title>
        <v-card-text>
          <v-list>
            <v-list-subheader>
              Vehicle configuration
            </v-list-subheader>
            <v-list-item>
              <v-list-item-title>
                Vehicle ID
              </v-list-item-title>
              <v-text-field variant="outlined" v-model="form.module_id" readonly density="compact">
              </v-text-field>
            </v-list-item>
            <v-list-subheader>
              Server V3 (MQTT) configuration
            </v-list-subheader>
            <v-list-item>
              <v-list-item-title>
                Server
              </v-list-item-title>
              <v-text-field variant="outlined" v-model="props.mqtt.hostname" readonly density="compact" />
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                TLS
              </v-list-item-title>
              <v-checkbox variant="outlined" v-model="props.mqtt.tls" readonly density="compact" />
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                Port
              </v-list-item-title>
              <v-text-field variant="outlined" v-model="props.mqtt.port" readonly density="compact" />
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                Username
              </v-list-item-title>
              <v-text-field variant="outlined" v-model="form.module_username" readonly density="compact" />
            </v-list-item>
            <v-list-item>
              <v-list-item-title>
                Password
              </v-list-item-title>
              <v-text-field variant="outlined" v-model="form.module_pwd" readonly density="compact">
              </v-text-field>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <Link :href="route('admin.vehicles.index')">
          <v-btn color="primary" append-icon="mdi-check">DONE</v-btn>
          </Link>
        </v-card-actions>
      </v-card>
      <v-alert title="Help us developping this app" type="info" icon="mdi-github">
        Add a direct config via HTTP API
        <a href="https://docs.openvehicles.com/en/latest/userguide/commands.html#module-http-server">
          Starting point
        </a>
      </v-alert>
    </v-dialog>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'VehiclesCreate',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'Vehicles',
          disabled: false,
          href: route('admin.vehicles.index'),
        },
        {
          title: 'Create',
          disabled: true,
        },
      ],
    }
  },
}
</script>
