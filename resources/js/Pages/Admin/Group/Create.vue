<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
const form = useForm({
  name: null,
})

const submit = () => {
  form.post('/admin/group', {
    onSuccess: () => {
      router.visit('/admin/group')
    },
  })
}
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">グループ新規登録</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.name" label="グループ名" variant="underlined" :error-messages="form.errors.name" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link href="/admin/group" as="div">
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
  methods: {
  },
  data() {
    return {
      breadcrumbs: [
        {
          title: 'グループ一覧',
          disabled: false,
          href: '/admin/group',
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
