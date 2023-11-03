<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import S3FileUpload from '@/Components/S3FileUpload.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
const form = useForm({
  name: null,
  email: null,
  password: null,
  phone: null,
  gender: null,
  address: null,
})

const submit = () => {
  form.post('/admin/user', {
    onSuccess: () => {
      router.visit('/admin/user')
    },
  })
}
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">メディア新規登録</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-text-field v-model="form.name" label="名前" variant="underlined" :error-messages="form.errors.name" />
          <S3FileUpload/>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link href="/admin/user" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
          <v-btn type="submit" color="primary">登録</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AdminLayout>
</template>

<script>
export default {
  props: {
  },
  props: {
  },
  methods: {
  },
  data() {
    return {
      genders: [
        { text: '男性', value: 'male' },
        { text: '女性', value: 'female' }, 
      ],
      breadcrumbs: [
        {
          title: 'メディア一覧',
          disabled: false,
          href: '/admin/media',
        },
        {
          title: '新規登録',
          disabled: true,
        },
      ],
    }
  },
}
</script>
