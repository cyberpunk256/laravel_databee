<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
})

const form = useForm({
  email: null,
  password: null,
  remember: false,
})
const showPassword = ref(false)

const submit = () => {
  form.post('/admin/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
<script>
export default {
  props: {
  },
  name: 'LoginPage',
}
</script>

<template>
  <GuestLayout>
    <Head title="ログイン" />
    <v-form @submit.prevent="submit">
      <div class="text-subtitle-1 text-medium-emphasis">メールアドレス</div>
      <v-text-field
        v-model="form.email"
        type="email"
        variant="outlined"
        density="compact"
        placeholder="user@gmail.com"
        prepend-inner-icon="mdi-email-outline"
        :error-messages="form.errors.email"
      />
      <v-text-field
        v-model="form.password"
        density="compact"
        variant="outlined"
        placeholder="●●●●●●●"
        prepend-inner-icon="mdi-lock-outline"
        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
        :type="showPassword ? 'text' : 'password'"
        :error-messages="form.errors.password"
        @click:append-inner="showPassword = !showPassword"
      />
      <v-checkbox v-model="form.remember" label="ログイン情報を記憶する" />

      <v-btn :loading="form.processing" type="submit" block color="primary" class="mb-12">ログイン</v-btn>
    </v-form>
  </GuestLayout>
</template>
