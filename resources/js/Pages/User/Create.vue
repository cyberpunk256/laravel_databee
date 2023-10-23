<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
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
  form.post('/user', {
    onSuccess: () => {
      router.visit('/user')
    },
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">ユーザー新規登録</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.name" label="名前" variant="underlined" :error-messages="form.errors.name" />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-select
                v-model="form.gender"
                :items="genders"
                item-title="text"
                item-value="value"
                label="性別"
                variant="underlined"
                :error-messages="form.errors.gender"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.email"
                label="メールアドレス"
                variant="underlined"
                type="email"
                :error-messages="form.errors.email"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.password"
                label="パースワード"
                variant="underlined"
                type="password"
                :error-messages="form.errors.password"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.phone"
                label="電話番号"
                variant="underlined"
                type="tel"
                :error-messages="form.errors.phone"
              />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <v-text-field
                v-model="form.address"
                label="住所"
                variant="underlined"
                :error-messages="form.errors.address"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link href="/user" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
          <v-btn type="submit" color="primary">登録</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AuthenticatedLayout>
</template>

<script>
export default {
  methods: {
  },
  name: 'UserCreate',
  data() {
    return {
      genders: [
        { text: '男性', value: 'male' },
        { text: '女性', value: 'female' }, 
      ],
      breadcrumbs: [
        {
          title: 'ユーザー一覧',
          disabled: false,
          href: '/user',
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
