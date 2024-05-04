<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
  user: {
    type: Object,
    required: true,
  }
})

const form = useForm({
  name: null,
  email: props.user.email,
  role: props.user.role,
  password: '',
  password_confirmation: '',
})
const showPassword = ref(false)

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
<script>
export default {
  name: 'RegisterPage',
}
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />
    <v-form @submit.prevent="submit">
      <div class="text-subtitle-1 text-medium-emphasis">Name</div>
      <v-text-field
        v-model="form.name"
        type="text"
        variant="outlined"
        density="compact"
        placeholder="Full name"
        prepend-inner-icon="mdi-account"
        :error-messages="form.errors.name"
      />
      <div class="text-subtitle-1 text-medium-emphasis">Email</div>
      <v-text-field
        v-model="form.email"
        type="email"
        variant="outlined"
        density="compact"
        placeholder="Email address"
        prepend-inner-icon="mdi-email-outline"
        :error-messages="form.errors.email"
        readonly
      />
      <div class="text-subtitle-1 text-medium-emphasis">Role</div>
      <v-text-field
        v-model="form.role"
        type="text"
        variant="outlined"
        density="compact"
        placeholder="Role"
        prepend-inner-icon="mdi-account-cowboy-hat"
        :error-messages="form.errors.role"
        readonly
      />
      <div class="text-subtitle-1 text-medium-emphasis">Password</div>
      <v-text-field
        v-model="form.password"
        density="compact"
        variant="outlined"
        placeholder="Enter your password"
        prepend-inner-icon="mdi-lock-outline"
        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
        :type="showPassword ? 'text' : 'password'"
        :error-messages="form.errors.password"
        @click:append-inner="showPassword = !showPassword"
      />
      <div class="text-subtitle-1 text-medium-emphasis">Password Confirmation</div>
      <v-text-field
        v-model="form.password_confirmation"
        density="compact"
        variant="outlined"
        placeholder="Enter your password"
        prepend-inner-icon="mdi-lock-outline"
        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
        :type="showPassword ? 'text' : 'password'"
        :error-messages="form.errors.password_confirmation"
        @click:append-inner="showPassword = !showPassword"
      />

      <v-btn :loading="form.processing" type="submit" block color="primary" class="mb-5 mt-3">Register</v-btn>
    </v-form>
  </GuestLayout>
</template>
