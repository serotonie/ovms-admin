<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { nanoid } from 'nanoid'
import { ref, watch } from 'vue'

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
  },
  existingVehicle: {
    type: Object,
    required: false,
    default: null
  }
})

const currentUserId = props.auth?.user?.id ?? null

const initialValues = {
  module_username: props.existingVehicle?.module_username ?? nanoid(12),
  module_pwd: '',
}

const form = useForm({
  name: 'home-assistant',
  owner: currentUserId,
  main_user: currentUserId,
  users: [],
  module_username: initialValues.module_username,
  module_pwd: initialValues.module_pwd,
})

const creation_success_dialog = ref(false)
const hasChanges = ref(false)

const updateHasChanges = () => {
  const currentValues = {
    module_username: form.module_username,
    module_pwd: form.module_pwd,
  }

  hasChanges.value = JSON.stringify(currentValues) !== JSON.stringify(initialValues)
}

watch(() => [form.module_username, form.module_pwd], updateHasChanges, { immediate: true })

const submit = () => {
  form.post(route('admin.home-assistant.store'), {
    preserveState: true,
    forceFormData: true,
    onSuccess: () => {
      creation_success_dialog.value = true
    }
  })
}
</script>

<template>
  <Head title="Home Assistant" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Home Assistant</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.module_username" append-inner-icon="mdi-reload"
                @click:append-inner="form.module_username = nanoid(12)" label="MQTT Username" variant="underlined"
                :error-messages="form.errors.module_username" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.module_pwd" append-inner-icon="mdi-reload"
                @click:append-inner="form.module_pwd = nanoid()" label="MQTT Password" variant="underlined"
                :error-messages="form.errors.module_pwd" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link :href="route('admin.vehicles.index')" as="div">
            <v-btn text>Cancel</v-btn>
          </Link>
          <v-btn type="submit" color="primary" append-icon="mdi-content-save" :loading="form.processing" :disabled="!hasChanges">Save</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>

    <v-dialog v-model="creation_success_dialog" persistent width="600">
      <v-card>
        <v-card-title>Home Assistant created</v-card-title>
        <v-card-text>
          <p>The config for Home Assistant has been successfully added.</p>
          <p class="mt-3">Use the following values to configure MQTT.</p>
          <v-list>
            <v-list-item>
              <v-list-item-title>Username</v-list-item-title>
              <v-text-field v-model="form.module_username" readonly density="compact" />
            </v-list-item>
            <v-list-item>
              <v-list-item-title>Password</v-list-item-title>
              <v-text-field v-model="form.module_pwd" readonly density="compact" />
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <Link :href="route('admin.home-assistant.index')">
            <v-btn color="primary">Done</v-btn>
          </Link>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'HomeAssistantIndex',
  data() {
    return {
      breadcrumbs: [
        {
          title: 'Home Assistant',
          disabled: true,
        },
      ],
    }
  },
}
</script>
