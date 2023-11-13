<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Breadcrumbs.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  person: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  name: props.person.name,
})

const submit = () => {
  form.patch('/admin/group/' + props.person.id, {
    onSuccess: () => {
      router.visit('/admin/group')
    },
  })
}
</script>

<template>
  <AdminLayout>
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">グループ編集</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card>
      <v-form @submit.prevent="submit">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="6">
              <v-text-field v-model="form.name" label="名前" variant="underlined" :error-messages="form.errors.name" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <Link href="/admin/group" as="div">
            <v-btn text>キャンセル</v-btn>
          </Link>
          <v-btn type="submit" color="primary">更新</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </AdminLayout>
</template>

<script>
export default {
  data() {
    return {
      breadcrumbs: [
        {
          title: 'グループ一覧',
          disabled: false,
          href: '/admin/group',
        },
        {
          title: '編集',
          disabled: true,
        },
      ],
    }
  },
}
</script>
